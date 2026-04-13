/**
 * Converts a JSON export from the Prisma Data Platform (console.prisma.io)
 * into a D1-compatible SQL file.
 *
 * Usage:
 *   node scripts/json-to-d1.mjs <path-to-export.json>
 *
 * The JSON file should be an object whose keys are table names and values
 * are arrays of row objects, e.g.:
 *   {
 *     "stores": [ { "id": "...", "name": "...", ... }, ... ],
 *     "products": [ ... ],
 *     ...
 *   }
 *
 * You can also pass a directory of per-table JSON files:
 *   node scripts/json-to-d1.mjs ./export-dir/
 * where each file is named <table>.json and contains an array of rows.
 */

import { readFileSync, readdirSync, statSync, writeFileSync } from "fs";
import { resolve, join, basename, extname } from "path";

// Insertion order matters for foreign keys
const TABLE_ORDER = [
  "stores",
  "site_settings",
  "navigation_links",
  "hero_slides",
  "hero_side_cards",
  "trust_features",
  "categories",
  "home_category_groups",
  "home_category_group_items",
  "profiles",
  "products",
  "product_media",
  "product_variants",
  "product_specs",
  "product_bullets",
  "product_relations",
  "reviews",
  "wishlists",
  "wishlist_items",
];

function lit(value) {
  if (value === null || value === undefined) return "NULL";
  if (typeof value === "boolean") return value ? "1" : "0";
  if (typeof value === "number") return String(value);
  // ISO date strings — keep as text
  if (typeof value === "string") return `'${value.replace(/'/g, "''")}'`;
  // Objects / arrays — JSON-stringify
  return `'${JSON.stringify(value).replace(/'/g, "''")}'`;
}

function insertRow(table, row) {
  const cols = Object.keys(row).map((k) => `"${k}"`).join(", ");
  const vals = Object.values(row).map((v) => lit(v)).join(", ");
  return `INSERT OR IGNORE INTO "${table}" (${cols}) VALUES (${vals});`;
}

function tableToLines(table, rows) {
  if (!rows || rows.length === 0) return [`-- ${table} (empty)`];
  const lines = [`-- ${table} (${rows.length} rows)`];

  if (table === "categories") {
    // Pass 1: insert without parent_id
    lines.push("-- pass 1: no parent links");
    for (const row of rows) {
      lines.push(insertRow(table, { ...row, parent_id: null }));
    }
    // Pass 2: restore parent links
    lines.push("-- pass 2: restore parent links");
    for (const row of rows) {
      if (row.parent_id) {
        lines.push(
          `UPDATE "categories" SET "parent_id" = ${lit(row.parent_id)} WHERE "id" = ${lit(row.id)};`
        );
      }
    }
  } else {
    for (const row of rows) {
      lines.push(insertRow(table, row));
    }
  }
  return lines;
}

function loadData(inputPath) {
  const resolved = resolve(inputPath);
  const stat = statSync(resolved);

  if (stat.isDirectory()) {
    // Directory of per-table JSON files
    const data = {};
    for (const file of readdirSync(resolved)) {
      if (extname(file) !== ".json") continue;
      const table = basename(file, ".json");
      const content = JSON.parse(readFileSync(join(resolved, file), "utf8"));
      data[table] = Array.isArray(content) ? content : content.data ?? content.rows ?? [];
    }
    return data;
  }

  // Single JSON file
  const content = JSON.parse(readFileSync(resolved, "utf8"));

  // Handle common export formats:
  // 1. { table: rows[] }  (ideal)
  // 2. [ { tableName, rows } ]  (some exporters)
  if (Array.isArray(content)) {
    const data = {};
    for (const entry of content) {
      const name = entry.tableName || entry.table || entry.name;
      const rows = entry.rows || entry.data || [];
      if (name) data[name] = rows;
    }
    return data;
  }
  return content;
}

const inputArg = process.argv[2];
if (!inputArg) {
  console.error("Usage: node scripts/json-to-d1.mjs <export.json | export-dir/>");
  process.exit(1);
}

const data = loadData(inputArg);
const tables = new Set(Object.keys(data));

const lines = [
  "-- D1 migration from JSON export",
  `-- Generated: ${new Date().toISOString()}`,
  "",
  "PRAGMA foreign_keys = OFF;",
  "",
];

// Emit tables in FK-safe order first
for (const table of TABLE_ORDER) {
  if (!tables.has(table)) continue;
  lines.push(...tableToLines(table, data[table]), "");
  tables.delete(table);
}

// Emit any remaining tables not in the known order
for (const table of tables) {
  lines.push(...tableToLines(table, data[table]), "");
}

lines.push("PRAGMA foreign_keys = ON;", "");

const out = resolve("scripts/migration-data.sql");
writeFileSync(out, lines.join("\n"), "utf8");

const rowCount = lines.filter((l) => l.startsWith("INSERT")).length;
console.log(`Converted ${rowCount} rows → scripts/migration-data.sql`);
console.log("\nApply to D1:");
console.log("  wrangler d1 execute modern-db --file=scripts/migration-data.sql");
