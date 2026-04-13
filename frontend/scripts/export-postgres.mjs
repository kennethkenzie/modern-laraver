/**
 * Exports all data from the old Prisma Postgres database using the Prisma client.
 * Writes scripts/migration-data.sql ready to be applied to D1.
 *
 * Run with:
 *   node scripts/export-postgres.mjs
 */

// Use the separately-generated PostgreSQL client (scripts/pg-schema.prisma)
import { PrismaClient } from "../node_modules/.prisma/pg-export-client/index.js";
import { PrismaPg } from "@prisma/adapter-pg";
import pkg from "pg";
import { writeFileSync } from "fs";
import { fileURLToPath } from "url";
import { dirname, join } from "path";

const { Pool } = pkg;
const __dirname = dirname(fileURLToPath(import.meta.url));

const DATABASE_URL =
  "postgres://a1c0c6def41c6f5c533cf2db56dfb0cd3141ef2b3ccc8f32aaf5b3ac2e77ae8e:sk_ydgxlCz-ZBW6PTSYVtwst@db.prisma.io:5432/postgres?sslmode=require";

const pool = new Pool({
  connectionString: DATABASE_URL,
  max: 1,
  ssl: { rejectUnauthorized: false },
});

const adapter = new PrismaPg(pool);
const prisma = new PrismaClient({ adapter });

/** Escape a value for SQLite INSERT */
function lit(value) {
  if (value === null || value === undefined) return "NULL";
  if (typeof value === "boolean") return value ? "1" : "0";
  if (typeof value === "number") return String(value);
  if (value instanceof Date) return `'${value.toISOString().replace(/'/g, "''")}'`;
  if (typeof value === "object") return `'${JSON.stringify(value).replace(/'/g, "''")}'`;
  return `'${String(value).replace(/'/g, "''")}'`;
}

function insert(table, row) {
  const cols = Object.keys(row).map((k) => `"${k}"`).join(", ");
  const vals = Object.values(row).map((v) => lit(v)).join(", ");
  return `INSERT OR IGNORE INTO "${table}" (${cols}) VALUES (${vals});`;
}

async function exportTable(table, rows) {
  const lines = [`-- ${table} (${rows.length} rows)`];
  for (const row of rows) {
    lines.push(insert(table, row));
  }
  return lines;
}

// Map Prisma model names to SQL table names
async function main() {
  console.log("Connecting to Prisma Postgres...");

  const lines = [
    "-- D1 data migration",
    `-- Generated: ${new Date().toISOString()}`,
    "",
    "PRAGMA foreign_keys = OFF;",
    "",
  ];

  // stores
  const stores = await prisma.store.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("stores", stores.map(r => ({
    id: r.id, name: r.name, slug: r.slug,
    support_email: r.supportEmail, support_phone: r.supportPhone,
    logo_url: r.logoUrl, is_active: r.isActive,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // site_settings
  const settings = await prisma.siteSettings.findMany();
  lines.push(...await exportTable("site_settings", settings.map(r => ({
    key: r.key,
    value: typeof r.value === "string" ? r.value : JSON.stringify(r.value),
    description: r.description,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // navigation_links
  const navLinks = await prisma.navigationLink.findMany({ orderBy: { sortOrder: "asc" } });
  lines.push(...await exportTable("navigation_links", navLinks.map(r => ({
    id: r.id, kind: r.kind, label: r.label, href: r.href,
    icon: r.icon, badge_text: r.badgeText, sort_order: r.sortOrder,
    is_active: r.isActive, created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // hero_slides
  const heroSlides = await prisma.heroSlide.findMany({ orderBy: { sortOrder: "asc" } });
  lines.push(...await exportTable("hero_slides", heroSlides.map(r => ({
    id: r.id, image_url: r.imageUrl, cta_label: r.ctaLabel, cta_href: r.ctaHref,
    sort_order: r.sortOrder, is_active: r.isActive,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // hero_side_cards
  const heroSideCards = await prisma.heroSideCard.findMany({ orderBy: { sortOrder: "asc" } });
  lines.push(...await exportTable("hero_side_cards", heroSideCards.map(r => ({
    id: r.id, eyebrow: r.eyebrow, title: r.title, image_url: r.imageUrl,
    href: r.href, sort_order: r.sortOrder, is_active: r.isActive,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // trust_features
  const trustFeatures = await prisma.trustFeature.findMany({ orderBy: { sortOrder: "asc" } });
  lines.push(...await exportTable("trust_features", trustFeatures.map(r => ({
    id: r.id, icon: r.icon, title: r.title, subtitle: r.subtitle,
    sort_order: r.sortOrder, is_active: r.isActive,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // categories — pass 1: all with parent_id = NULL
  const categories = await prisma.category.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(`-- categories — pass 1: insert without parent links`);
  for (const r of categories) {
    lines.push(insert("categories", {
      id: r.id, parent_id: null, name: r.name, slug: r.slug,
      description: r.description, image_url: r.imageUrl,
      is_active: r.isActive, featured_on_home: r.featuredOnHome,
      featured_sort_order: r.featuredSortOrder,
      created_at: r.createdAt, updated_at: r.updatedAt,
    }));
  }
  lines.push(`-- categories — pass 2: restore parent links`);
  for (const r of categories) {
    if (r.parentId) {
      lines.push(`UPDATE "categories" SET "parent_id" = ${lit(r.parentId)} WHERE "id" = ${lit(r.id)};`);
    }
  }
  lines.push("");

  // home_category_groups
  const homeCatGroups = await prisma.homeCategoryGroup.findMany({ orderBy: { sortOrder: "asc" } });
  lines.push(...await exportTable("home_category_groups", homeCatGroups.map(r => ({
    id: r.id, title: r.title, cta_label: r.ctaLabel, cta_href: r.ctaHref,
    sort_order: r.sortOrder, is_active: r.isActive,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // home_category_group_items
  const homeCatGroupItems = await prisma.homeCategoryGroupItem.findMany({ orderBy: { sortOrder: "asc" } });
  lines.push(...await exportTable("home_category_group_items", homeCatGroupItems.map(r => ({
    id: r.id, group_id: r.groupId, category_id: r.categoryId,
    label: r.label, image_url: r.imageUrl, href: r.href,
    sort_order: r.sortOrder, is_active: r.isActive,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // profiles
  const profiles = await prisma.profile.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("profiles", profiles.map(r => ({
    id: r.id, store_id: r.storeId, email: r.email, full_name: r.fullName,
    phone: r.phone, role: r.role, avatar_url: r.avatarUrl,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // products
  const products = await prisma.product.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("products", products.map(r => ({
    id: r.id, store_id: r.storeId, category_id: r.categoryId,
    slug: r.slug, name: r.name, short_description: r.shortDescription,
    description: r.description, brand: r.brand, currency_code: r.currencyCode,
    list_price: r.listPrice !== null ? Number(r.listPrice) : null,
    sale_price: r.salePrice !== null ? Number(r.salePrice) : null,
    average_rating: Number(r.averageRating), rating_count: r.ratingCount,
    bestseller_label: r.bestsellerLabel, bestseller_category: r.bestsellerCategory,
    bought_past_month_label: r.boughtPastMonthLabel, shipping_label: r.shippingLabel,
    in_stock_label: r.inStockLabel, delivery_label: r.deliveryLabel,
    returns_label: r.returnsLabel, payment_label: r.paymentLabel,
    is_published: r.isPublished, is_featured_home: r.isFeaturedHome,
    home_sort_order: r.homeSortOrder, published_at: r.publishedAt,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // product_media
  const productMedia = await prisma.productMedia.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("product_media", productMedia.map(r => ({
    id: r.id, product_id: r.productId, kind: r.kind, url: r.url,
    alt_text: r.altText, is_primary: r.isPrimary, sort_order: r.sortOrder,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // product_variants
  const productVariants = await prisma.productVariant.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("product_variants", productVariants.map(r => ({
    id: r.id, product_id: r.productId, sku: r.sku,
    option_name: r.optionName, option_value: r.optionValue,
    price: Number(r.price),
    compare_at_price: r.compareAtPrice !== null ? Number(r.compareAtPrice) : null,
    stock_qty: r.stockQty, is_default: r.isDefault, is_active: r.isActive,
    sort_order: r.sortOrder, created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // product_specs
  const productSpecs = await prisma.productSpec.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("product_specs", productSpecs.map(r => ({
    id: r.id, product_id: r.productId, spec_name: r.specName,
    spec_value: r.specValue, sort_order: r.sortOrder,
    created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // product_bullets
  const productBullets = await prisma.productBullet.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("product_bullets", productBullets.map(r => ({
    id: r.id, product_id: r.productId, bullet_text: r.bulletText,
    sort_order: r.sortOrder, created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // product_relations
  const productRelations = await prisma.productRelation.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("product_relations", productRelations.map(r => ({
    id: r.id, product_id: r.productId, related_product_id: r.relatedProductId,
    relation_kind: r.relationKind, badge_text: r.badgeText,
    sort_order: r.sortOrder, created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // reviews
  const reviews = await prisma.review.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("reviews", reviews.map(r => ({
    id: r.id, product_id: r.productId, user_id: r.userId,
    rating: r.rating, title: r.title, body: r.body,
    is_approved: r.isApproved, created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // wishlists
  const wishlists = await prisma.wishlist.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("wishlists", wishlists.map(r => ({
    id: r.id, user_id: r.userId, name: r.name,
    is_default: r.isDefault, created_at: r.createdAt, updated_at: r.updatedAt,
  }))), "");

  // wishlist_items
  const wishlistItems = await prisma.wishlistItem.findMany({ orderBy: { createdAt: "asc" } });
  lines.push(...await exportTable("wishlist_items", wishlistItems.map(r => ({
    id: r.id, wishlist_id: r.wishlistId, product_id: r.productId,
    created_at: r.createdAt,
  }))), "");

  lines.push("PRAGMA foreign_keys = ON;", "");

  const out = join(__dirname, "migration-data.sql");
  writeFileSync(out, lines.join("\n"), "utf8");

  const rowCount = lines.filter((l) => l.startsWith("INSERT")).length;
  console.log(`\nExported ${rowCount} rows → scripts/migration-data.sql`);
  console.log("\nNext:");
  console.log("  wrangler d1 execute modern-db --file=scripts/migration-data.sql");

  await prisma.$disconnect();
  await pool.end();
}

main().catch(async (err) => {
  console.error("Export failed:", err.message);
  await prisma.$disconnect().catch(() => {});
  await pool.end().catch(() => {});
  process.exit(1);
});
