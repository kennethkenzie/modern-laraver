"use client";

import Link from "next/link";
import { useEffect, useMemo, useState } from "react";
import {
  Eye,
  Loader2,
  Pencil,
  Plus,
  Search,
  Star,
  Trash2,
} from "lucide-react";

type ProductRow = {
  id: string;
  name: string;
  shortDescription: string;
  slug: string;
  category: string;
  price: number;
  stock: number;
  image: string;
  isPublished: boolean;
  isFeatured: boolean;
  createdAt: string;
};

const PAGE_SIZE = 12;

type NoticeState =
  | {
      tone: "success" | "error";
      text: string;
    }
  | null;

function formatCurrency(value: number) {
  return new Intl.NumberFormat("en-UG", {
    style: "currency",
    currency: "UGX",
    minimumFractionDigits: 0,
  }).format(value);
}

export default function ProductsListClient({ initialProducts }: { initialProducts: ProductRow[] }) {
  const [products, setProducts] = useState(initialProducts);
  const [searchQuery, setSearchQuery] = useState("");
  const [filterCategory, setFilterCategory] = useState("All");
  const [currentPage, setCurrentPage] = useState(1);
  const [busyAction, setBusyAction] = useState<string | null>(null);
  const [notice, setNotice] = useState<NoticeState>(null);

  useEffect(() => {
    if (!notice) return;
    const timeout = window.setTimeout(() => setNotice(null), 3200);
    return () => window.clearTimeout(timeout);
  }, [notice]);

  const categories = useMemo(() => {
    const set = new Set(products.map((product) => product.category));
    return ["All", ...Array.from(set)];
  }, [products]);

  const filteredProducts = useMemo(() => {
    return products.filter((product) => {
      const query = searchQuery.trim().toLowerCase();
      const matchesSearch =
        !query ||
        product.name.toLowerCase().includes(query) ||
        product.slug.toLowerCase().includes(query);
      const matchesCategory =
        filterCategory === "All" || product.category === filterCategory;
      return matchesSearch && matchesCategory;
    });
  }, [products, searchQuery, filterCategory]);

  const totalPages = Math.max(1, Math.ceil(filteredProducts.length / PAGE_SIZE));
  const safeCurrentPage = Math.min(currentPage, totalPages);
  const paginatedProducts = filteredProducts.slice(
    (safeCurrentPage - 1) * PAGE_SIZE,
    safeCurrentPage * PAGE_SIZE
  );

  const stats = useMemo(
    () => ({
      total: products.length,
      published: products.filter((product) => product.isPublished).length,
      featured: products.filter((product) => product.isFeatured).length,
      lowStock: products.filter((product) => product.stock > 0 && product.stock <= 5).length,
    }),
    [products]
  );

  const setProductPatch = (id: string, patch: Partial<ProductRow>) => {
    setProducts((current) =>
      current.map((product) => (product.id === id ? { ...product, ...patch } : product))
    );
  };

  const removeProduct = (id: string) => {
    setProducts((current) => current.filter((product) => product.id !== id));
  };

  const withBusy = async (key: string, task: () => Promise<void>) => {
    setBusyAction(key);
    try {
      await task();
    } catch (error) {
      console.error(error);
      setNotice({
        tone: "error",
        text: error instanceof Error ? error.message : "Request failed.",
      });
    } finally {
      setBusyAction(null);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Delete this product?")) return;

    await withBusy(`delete:${id}`, async () => {
      const response = await fetch(`/api/admin/products/${id}`, { method: "DELETE" });
      if (!response.ok) throw new Error("Failed to delete product.");
      removeProduct(id);
      setNotice({ tone: "success", text: "Product deleted successfully." });
    });
  };

  const handlePublishToggle = async (product: ProductRow) => {
    await withBusy(`publish:${product.id}`, async () => {
      const response = await fetch(`/api/admin/products/${product.id}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "set_publish", isPublished: !product.isPublished }),
      });
      if (!response.ok) throw new Error("Failed to update product status.");
      setProductPatch(product.id, { isPublished: !product.isPublished });
      setNotice({
        tone: "success",
        text: !product.isPublished ? "Product published successfully." : "Product moved to draft successfully.",
      });
    });
  };

  const handleFeaturedToggle = async (product: ProductRow) => {
    await withBusy(`featured:${product.id}`, async () => {
      const response = await fetch(`/api/admin/products/${product.id}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "set_featured", isFeatured: !product.isFeatured }),
      });
      if (!response.ok) throw new Error("Failed to update featured state.");
      setProductPatch(product.id, { isFeatured: !product.isFeatured });
      setNotice({
        tone: "success",
        text: !product.isFeatured ? "Product featured successfully." : "Product removed from featured successfully.",
      });
    });
  };

  const visibleRangeStart = filteredProducts.length === 0 ? 0 : (safeCurrentPage - 1) * PAGE_SIZE + 1;
  const visibleRangeEnd = Math.min(filteredProducts.length, safeCurrentPage * PAGE_SIZE);

  return (
    <div className="space-y-6">
      {notice ? (
        <div
          className={`rounded-2xl border px-4 py-3 text-sm font-medium ${
            notice.tone === "success"
              ? "border-[#bbf7d0] bg-[#f0fdf4] text-[#166534]"
              : "border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]"
          }`}
        >
          {notice.text}
        </div>
      ) : null}

      <section className="rounded-[28px] border border-[#e3e6ea] bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.05)] sm:p-6">
        <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
          <div className="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px]">
            <div className="relative flex items-center overflow-hidden rounded-full border border-[#d5d9d9] bg-[#f7f8fa] px-4 focus-within:border-[#f59e0b] focus-within:bg-white">
              <Search className="h-4 w-4 text-gray-400" />
              <input
                type="text"
                value={searchQuery}
                onChange={(event) => {
                  setSearchQuery(event.target.value);
                  setCurrentPage(1);
                }}
                placeholder="Search by product name or slug"
                className="h-12 w-full bg-transparent px-3 text-[14px] text-gray-700 outline-none placeholder:text-gray-400"
              />
            </div>

            <select
              value={filterCategory}
              onChange={(event) => {
                setFilterCategory(event.target.value);
                setCurrentPage(1);
              }}
              className="h-12 rounded-full border border-[#d5d9d9] bg-white px-4 text-[14px] text-gray-700 outline-none hover:border-[#aab7c4] focus:border-[#f59e0b]"
            >
              {categories.map((category) => (
                <option key={category} value={category}>
                  {category}
                </option>
              ))}
            </select>
          </div>

          <div className="flex flex-wrap items-center gap-3">
            <Link
              href="/admin/products/add"
              className="inline-flex h-12 items-center gap-2 rounded-full bg-[#131921] px-5 text-[14px] font-bold text-white transition hover:bg-black"
            >
              <Plus className="h-4 w-4" />
              Add Product
            </Link>
          </div>
        </div>
      </section>

      <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Catalog Items" value={stats.total.toString()} tone="neutral" />
        <StatCard label="Published" value={stats.published.toString()} tone="success" />
        <StatCard label="Featured" value={stats.featured.toString()} tone="warning" />
        <StatCard label="Low Stock" value={stats.lowStock.toString()} tone="danger" />
      </section>

      <section className="overflow-hidden rounded-[30px] border border-[#e3e6ea] bg-white shadow-[0_20px_50px_rgba(15,23,42,0.06)]">
        <div className="border-b border-[#edf0f2] bg-[linear-gradient(180deg,#ffffff_0%,#fafbfc_100%)] px-5 py-4 sm:px-6">
          <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 className="text-[20px] font-bold text-[#111827]">Product inventory</h2>
              <p className="text-[13px] text-gray-500">
                Showing {visibleRangeStart}-{visibleRangeEnd} of {filteredProducts.length} matching products
              </p>
            </div>
            <div className="inline-flex rounded-full bg-[#f3f4f6] px-3 py-1 text-[12px] font-semibold text-gray-600">
              Page {safeCurrentPage} of {totalPages}
            </div>
          </div>
        </div>

        {paginatedProducts.length === 0 ? (
          <div className="px-6 py-20 text-center">
            <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#f3f4f6] text-gray-400">
              <Search className="h-7 w-7" />
            </div>
            <h3 className="mt-4 text-[18px] font-bold text-gray-900">No products found</h3>
            <p className="mt-2 text-[14px] text-gray-500">
              Adjust the search or category filter to find products faster.
            </p>
          </div>
        ) : (
          <>
            <div className="hidden xl:block">
              <table className="w-full text-left">
                <thead>
                  <tr className="border-b border-[#edf0f2] bg-[#fafafa] text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">
                    <th className="px-6 py-4">Product</th>
                    <th className="px-6 py-4">Category</th>
                    <th className="px-6 py-4">Price</th>
                    <th className="px-6 py-4">Stock</th>
                    <th className="px-6 py-4">State</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {paginatedProducts.map((product) => (
                    <tr key={product.id} className="border-b border-[#f2f4f7] last:border-b-0 hover:bg-[#fcfcfd]">
                      <td className="px-6 py-5">
                        <ProductIdentity product={product} />
                      </td>
                      <td className="px-6 py-5">
                        <span className="inline-flex rounded-full bg-[#f3f4f6] px-3 py-1 text-[12px] font-semibold text-gray-600">
                          {product.category}
                        </span>
                      </td>
                      <td className="px-6 py-5 text-[15px] font-bold text-[#111827]">
                        {formatCurrency(product.price)}
                      </td>
                      <td className="px-6 py-5">
                        <StockPill stock={product.stock} />
                      </td>
                      <td className="px-6 py-5">
                        <div className="flex flex-wrap gap-2">
                          <StatusPill active={product.isPublished} activeLabel="Published" inactiveLabel="Draft" />
                          <StatusPill active={product.isFeatured} activeLabel="Featured" inactiveLabel="Standard" tone="warning" />
                        </div>
                      </td>
                      <td className="px-6 py-5">
                        <div className="flex justify-end">
                          <ProductActions
                            product={product}
                            busyAction={busyAction}
                            onPublishToggle={handlePublishToggle}
                            onFeaturedToggle={handleFeaturedToggle}
                            onDelete={handleDelete}
                          />
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="grid gap-4 p-4 sm:p-5 xl:hidden">
              {paginatedProducts.map((product) => (
                <article
                  key={product.id}
                  className="rounded-[24px] border border-[#e7ebef] bg-[#fcfcfd] p-4 shadow-[0_10px_24px_rgba(15,23,42,0.04)]"
                >
                  <ProductIdentity product={product} compact />
                  <div className="mt-4 grid grid-cols-2 gap-3 text-[13px]">
                    <InfoTile label="Category" value={product.category} />
                    <InfoTile label="Price" value={formatCurrency(product.price)} />
                    <InfoTile label="Stock" value={`${product.stock} units`} />
                    <InfoTile label="Added" value={new Date(product.createdAt).toLocaleDateString()} />
                  </div>
                  <div className="mt-4 flex flex-wrap gap-2">
                    <StatusPill active={product.isPublished} activeLabel="Published" inactiveLabel="Draft" />
                    <StatusPill active={product.isFeatured} activeLabel="Featured" inactiveLabel="Standard" tone="warning" />
                  </div>
                  <div className="mt-4">
                    <ProductActions
                      product={product}
                      busyAction={busyAction}
                      onPublishToggle={handlePublishToggle}
                      onFeaturedToggle={handleFeaturedToggle}
                      onDelete={handleDelete}
                      stacked
                    />
                  </div>
                </article>
              ))}
            </div>
          </>
        )}

        <div className="flex flex-col gap-3 border-t border-[#edf0f2] bg-[#fafbfc] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
          <p className="text-[13px] text-gray-500">
            {filteredProducts.length === 0
              ? "No products match the current filters."
              : `Showing ${visibleRangeStart}-${visibleRangeEnd} of ${filteredProducts.length} products`}
          </p>
          <Pagination
            currentPage={safeCurrentPage}
            totalPages={totalPages}
            onChange={setCurrentPage}
          />
        </div>
      </section>
    </div>
  );
}

function ProductIdentity({
  product,
  compact = false,
}: {
  product: ProductRow;
  compact?: boolean;
}) {
  return (
    <div className="flex items-center gap-4">
      <div className={`overflow-hidden rounded-2xl border border-[#e5e7eb] bg-white ${compact ? "h-16 w-16" : "h-18 w-18"} shrink-0`}>
        {product.image ? (
          <img src={product.image} alt={product.name} className="h-full w-full object-contain p-2" />
        ) : (
          <div className="flex h-full w-full items-center justify-center text-gray-300">
            <Plus className="h-5 w-5" />
          </div>
        )}
      </div>
      <div className="min-w-0">
        <div className={`${compact ? "text-[14px]" : "text-[15px]"} line-clamp-1 font-bold leading-6 text-[#111827]`}>
          {product.name}
        </div>
        <div className="mt-1 text-[12px] text-gray-400">{product.slug}</div>
      </div>
    </div>
  );
}

function ProductActions({
  product,
  busyAction,
  onPublishToggle,
  onFeaturedToggle,
  onDelete,
  stacked = false,
}: {
  product: ProductRow;
  busyAction: string | null;
  onPublishToggle: (product: ProductRow) => Promise<void>;
  onFeaturedToggle: (product: ProductRow) => Promise<void>;
  onDelete: (id: string) => Promise<void>;
  stacked?: boolean;
}) {
  const publishBusy = busyAction === `publish:${product.id}`;
  const featureBusy = busyAction === `featured:${product.id}`;
  const deleteBusy = busyAction === `delete:${product.id}`;

  return (
    <div className={`flex ${stacked ? "flex-col" : "flex-wrap"} gap-2`}>
      <Link
        href={`/admin/products/${product.id}`}
        className="inline-flex h-10 items-center justify-center gap-2 rounded-full border border-[#d5d9d9] bg-white px-4 text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb]"
      >
        <Eye className="h-4 w-4" />
        View
      </Link>
      <Link
        href={`/admin/products/add?edit=${product.id}`}
        className="inline-flex h-10 items-center justify-center gap-2 rounded-full border border-[#d5d9d9] bg-white px-4 text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb]"
      >
        <Pencil className="h-4 w-4" />
        Edit
      </Link>
      <button
        type="button"
        onClick={() => void onFeaturedToggle(product)}
        disabled={featureBusy}
        className={`inline-flex h-10 items-center justify-center gap-2 rounded-full px-4 text-[13px] font-semibold transition ${
          product.isFeatured
            ? "border border-[#fcd34d] bg-[#fff7d6] text-[#92400e] hover:bg-[#fef0b3]"
            : "border border-[#d5d9d9] bg-white text-gray-700 hover:bg-[#f8f9fb]"
        } disabled:opacity-50`}
      >
        {featureBusy ? <Loader2 className="h-4 w-4 animate-spin" /> : <Star className={`h-4 w-4 ${product.isFeatured ? "fill-current" : ""}`} />}
        {product.isFeatured ? "Unfeature" : "Feature"}
      </button>
      <button
        type="button"
        onClick={() => void onPublishToggle(product)}
        disabled={publishBusy}
        className={`inline-flex h-10 items-center justify-center rounded-full px-4 text-[13px] font-semibold transition ${
          product.isPublished
            ? "border border-[#fdba74] bg-[#fff7ed] text-[#c2410c] hover:bg-[#ffedd5]"
            : "border border-[#86efac] bg-[#f0fdf4] text-[#166534] hover:bg-[#dcfce7]"
        } disabled:opacity-50`}
      >
        {publishBusy ? <Loader2 className="h-4 w-4 animate-spin" /> : product.isPublished ? "Unpublish" : "Publish"}
      </button>
      <button
        type="button"
        onClick={() => void onDelete(product.id)}
        disabled={deleteBusy}
        className="inline-flex h-10 items-center justify-center gap-2 rounded-full border border-[#fecaca] bg-[#fef2f2] px-4 text-[13px] font-semibold text-[#b91c1c] transition hover:bg-[#fee2e2] disabled:opacity-50"
      >
        {deleteBusy ? <Loader2 className="h-4 w-4 animate-spin" /> : <Trash2 className="h-4 w-4" />}
        Delete
      </button>
    </div>
  );
}

function StatusPill({
  active,
  activeLabel,
  inactiveLabel,
  tone = "success",
}: {
  active: boolean;
  activeLabel: string;
  inactiveLabel: string;
  tone?: "success" | "warning";
}) {
  const activeClass =
    tone === "warning"
      ? "border-[#fcd34d] bg-[#fff7d6] text-[#92400e]"
      : "border-[#86efac] bg-[#f0fdf4] text-[#166534]";

  return (
    <span
      className={`inline-flex rounded-full border px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] ${
        active ? activeClass : "border-[#e5e7eb] bg-[#f3f4f6] text-gray-500"
      }`}
    >
      {active ? activeLabel : inactiveLabel}
    </span>
  );
}

function StockPill({ stock }: { stock: number }) {
  const tone =
    stock <= 0
      ? "bg-[#fef2f2] text-[#b91c1c]"
      : stock <= 5
        ? "bg-[#fff7ed] text-[#c2410c]"
        : "bg-[#f0fdf4] text-[#166534]";

  return (
    <span className={`inline-flex rounded-full px-3 py-1 text-[12px] font-semibold ${tone}`}>
      {stock <= 0 ? "Out of stock" : `${stock} in stock`}
    </span>
  );
}

function StatCard({
  label,
  value,
  tone,
}: {
  label: string;
  value: string;
  tone: "neutral" | "success" | "warning" | "danger";
}) {
  const tones = {
    neutral: "from-[#111827] to-[#1f2937] text-white",
    success: "from-[#065f46] to-[#10b981] text-white",
    warning: "from-[#92400e] to-[#f59e0b] text-white",
    danger: "from-[#7f1d1d] to-[#ef4444] text-white",
  };

  return (
    <div className={`rounded-[24px] bg-gradient-to-br p-5 shadow-[0_14px_32px_rgba(15,23,42,0.08)] ${tones[tone]}`}>
      <div className="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">{label}</div>
      <div className="mt-3 text-[30px] font-bold tracking-tight">{value}</div>
    </div>
  );
}

function InfoTile({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-2xl bg-white px-4 py-3">
      <div className="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">{label}</div>
      <div className="mt-1 text-[13px] font-semibold text-gray-700">{value}</div>
    </div>
  );
}

function Pagination({
  currentPage,
  totalPages,
  onChange,
}: {
  currentPage: number;
  totalPages: number;
  onChange: (page: number) => void;
}) {
  const pages = useMemo(() => {
    const start = Math.max(1, currentPage - 2);
    const end = Math.min(totalPages, start + 4);
    return Array.from({ length: end - start + 1 }, (_, index) => start + index);
  }, [currentPage, totalPages]);

  return (
    <div className="flex flex-wrap items-center gap-2">
      <button
        type="button"
        onClick={() => onChange(Math.max(1, currentPage - 1))}
        disabled={currentPage === 1}
        className="inline-flex h-10 items-center justify-center rounded-full border border-[#d5d9d9] bg-white px-4 text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb] disabled:opacity-40"
      >
        Previous
      </button>
      {pages.map((page) => (
        <button
          key={page}
          type="button"
          onClick={() => onChange(page)}
          className={`inline-flex h-10 w-10 items-center justify-center rounded-full text-[13px] font-bold ${
            page === currentPage
              ? "bg-[#f59e0b] text-[#111827]"
              : "border border-[#d5d9d9] bg-white text-gray-700 hover:bg-[#f8f9fb]"
          }`}
        >
          {page}
        </button>
      ))}
      <button
        type="button"
        onClick={() => onChange(Math.min(totalPages, currentPage + 1))}
        disabled={currentPage === totalPages}
        className="inline-flex h-10 items-center justify-center rounded-full border border-[#d5d9d9] bg-white px-4 text-[13px] font-semibold text-gray-700 hover:bg-[#f8f9fb] disabled:opacity-40"
      >
        Next
      </button>
    </div>
  );
}
