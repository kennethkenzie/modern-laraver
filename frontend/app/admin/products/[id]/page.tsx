import Link from "next/link";
import { notFound } from "next/navigation";
import { apiFetch, ADMIN_API_TOKEN } from "@/lib/api";

type AdminProductDetail = {
  id: string; name: string; slug: string; brand?: string; currencyCode: string;
  isPublished: boolean; isFeaturedHome: boolean; createdAt: string;
  shortDescription?: string; description?: string;
  category?: { name: string };
  media: { id: string; url: string; kind: string; alt_text?: string }[];
  variants: { id: string; optionValue: string; sku?: string; price: string; stockQty: number }[];
  specs: { id: string; specName: string; specValue: string }[];
  bullets: { id: string; bulletText: string }[];
};

async function fetchAdminProductDetail(id: string): Promise<AdminProductDetail | null> {
  try {
    const data = await apiFetch<{ product: AdminProductDetail }>(
      `/admin/products/${id}`,
      { token: ADMIN_API_TOKEN || null }
    );
    // Normalise field names returned by Laravel vs what the JSX expects
    const p = data.product;
    return {
      ...p,
      media:    (p.media ?? []).map((m: any) => ({ ...m, altText: m.alt_text ?? m.altText })),
      variants: (p.variants ?? []).map((v: any) => ({ ...v, optionValue: v.label ?? v.optionValue, stockQty: Number(v.stockQty ?? v.stock_qty ?? 0) })),
      specs:    (p.specs ?? []).map((s: any) => ({ ...s, specName: s.label ?? s.specName, specValue: s.value ?? s.specValue })),
      bullets:  (p.bullets ?? []).map((b: any) => ({ ...b, bulletText: typeof b === "string" ? b : b.bulletText })),
    };
  } catch {
    return null;
  }
}

export default async function AdminProductDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const product = await fetchAdminProductDetail(id);

  if (!product) {
    notFound();
  }

  return (
    <div className="bg-[#f7f7f8] min-h-screen p-4 sm:p-6 lg:p-8">
      <div className="mx-auto max-w-[1200px] space-y-6">
        <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
          <div>
            <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#0b63ce]">Product View</p>
            <h1 className="mt-2 text-3xl font-bold tracking-tight text-gray-900">{product.name}</h1>
            <p className="mt-2 text-sm text-gray-500">{product.slug}</p>
          </div>
          <div className="flex items-center gap-3">
            <Link href={`/admin/products/add?edit=${product.id}`} className="inline-flex h-11 items-center justify-center rounded-md border border-gray-300 bg-white px-5 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
              Edit Product
            </Link>
            <Link href="/admin/products" className="inline-flex h-11 items-center justify-center rounded-md bg-[#1f2937] px-5 text-sm font-bold text-white transition hover:bg-black">
              Back to List
            </Link>
          </div>
        </div>

        <div className="grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
          <section className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 className="text-lg font-bold text-gray-900">Media</h2>
            <div className="mt-5 space-y-4">
              {product.media.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center text-sm text-gray-400">
                  No product media
                </div>
              ) : (
                product.media.map((item: (typeof product.media)[number]) => (
                  <div key={item.id} className="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50">
                    {item.kind === "image" ? (
                      <img src={item.url} alt={item.altText || product.name} className="h-48 w-full object-contain bg-white p-3" />
                    ) : (
                      <video src={item.url} className="h-48 w-full object-cover bg-black" controls />
                    )}
                  </div>
                ))
              )}
            </div>
          </section>

          <div className="space-y-6">
            <section className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
              <h2 className="text-lg font-bold text-gray-900">Overview</h2>
              <div className="mt-5 grid gap-4 md:grid-cols-2">
                <Info label="Category" value={product.category?.name || "Uncategorized"} />
                <Info label="Brand" value={product.brand || "Not set"} />
                <Info label="Currency" value={product.currencyCode} />
                <Info label="Status" value={product.isPublished ? "Published" : "Draft"} />
                <Info label="Featured" value={(product as any).isFeaturedHome ? "Yes" : "No"} />
                <Info label="Created" value={new Date(product.createdAt).toLocaleString()} />
              </div>
            </section>

            <section className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
              <h2 className="text-lg font-bold text-gray-900">Description</h2>
              <div className="mt-4 text-sm text-gray-700" dangerouslySetInnerHTML={{ __html: product.shortDescription || "No short description." }} />
              <div className="tinymce-content mt-4 rounded-2xl bg-gray-50 p-4 text-sm text-gray-700">
                {product.description ? (
                  <div dangerouslySetInnerHTML={{ __html: product.description }} />
                ) : (
                  "No full description."
                )}
              </div>
            </section>

            <section className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
              <h2 className="text-lg font-bold text-gray-900">Variants</h2>
              <div className="mt-4 space-y-3">
                {product.variants.map((variant: (typeof product.variants)[number]) => (
                  <div key={variant.id} className="grid gap-3 rounded-2xl border border-gray-200 bg-gray-50 p-4 md:grid-cols-4">
                    <Info label="Variant" value={variant.optionValue} />
                    <Info label="SKU" value={variant.sku || "Not set"} />
                    <Info label="Price" value={`${product.currencyCode} ${Number(variant.price).toLocaleString()}`} />
                    <Info label="Stock" value={variant.stockQty.toString()} />
                  </div>
                ))}
              </div>
            </section>

            <section className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
              <h2 className="text-lg font-bold text-gray-900">Specifications</h2>
              <div className="mt-4 grid gap-3 md:grid-cols-2">
                {product.specs.length === 0 ? (
                  <div className="text-sm text-gray-400">No specifications added.</div>
                ) : (
                  product.specs.map((spec: (typeof product.specs)[number]) => (
                    <div key={spec.id} className="rounded-2xl bg-gray-50 px-4 py-3">
                      <div className="text-xs font-bold uppercase tracking-wider text-gray-400">{spec.specName}</div>
                      <div className="mt-1 text-sm font-medium text-gray-800">{spec.specValue}</div>
                    </div>
                  ))
                )}
              </div>
            </section>

            <section className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
              <h2 className="text-lg font-bold text-gray-900">Bullets</h2>
              <ul className="mt-4 list-disc space-y-2 pl-5 text-sm text-gray-700">
                {product.bullets.length === 0 ? <li>No bullet points added.</li> : product.bullets.map((bullet: (typeof product.bullets)[number]) => <li key={bullet.id}>{bullet.bulletText}</li>)}
              </ul>
            </section>
          </div>
        </div>
      </div>
    </div>
  );
}

function ProductUnavailableState() {
  return (
    <div className="min-h-screen bg-[#f7f7f8] p-4 sm:p-6 lg:p-8">
      <div className="mx-auto max-w-[780px] rounded-[28px] border border-[#e5e7eb] bg-white p-8 shadow-sm">
        <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#0b63ce]">Product View</p>
        <h1 className="mt-3 text-3xl font-bold tracking-tight text-gray-900">
          Product details are temporarily unavailable
        </h1>
        <p className="mt-3 text-[15px] leading-7 text-gray-600">
          The product record could not be loaded from the database right now. This usually means the upstream
          database connection is temporarily unavailable.
        </p>
        <div className="mt-6 flex flex-wrap gap-3">
          <Link
            href="/admin/products"
            className="inline-flex h-11 items-center justify-center rounded-md bg-[#1f2937] px-5 text-sm font-bold text-white transition hover:bg-black"
          >
            Back to Product List
          </Link>
          <Link
            href="/admin"
            className="inline-flex h-11 items-center justify-center rounded-md border border-gray-300 bg-white px-5 text-sm font-bold text-gray-700 transition hover:bg-gray-50"
          >
            Return to Dashboard
          </Link>
        </div>
      </div>
    </div>
  );
}

function Info({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-2xl bg-gray-50 px-4 py-3">
      <div className="text-xs font-bold uppercase tracking-wider text-gray-400">{label}</div>
      <div className="mt-1 text-sm font-medium text-gray-800">{value}</div>
    </div>
  );
}
