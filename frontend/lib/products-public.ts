/**
 * Server-side public product helpers — fetch from the Laravel API instead of Prisma.
 */
import { apiFetch } from "@/lib/api";
import type { ProductSpec, RelatedProduct } from "@/lib/frontend-data";

// ─── shared types ─────────────────────────────────────────────

export type PublicProductVariant = {
  id: string;
  label: string;
  price: number;
  priceLabel: string;
  oldPrice?: string;
  stockQty: number;
  sku?: string;
  isDefault: boolean;
};

export type PublicProductGalleryItem = {
  id: string;
  image: string;
  alt: string;
  isVideo?: boolean;
};

export type PublicProductPageData = {
  id: string;
  categoryId?: string;
  slug: string;
  name: string;
  shortDescription: string;
  description: string;
  brand?: string;
  currencyCode: string;
  storeName: string;
  storeLabel: string;
  rating: number;
  ratingsLabel: string;
  bestsellerLabel?: string;
  bestsellerCategory?: string;
  boughtLabel?: string;
  shippingLabel: string;
  inStockLabel: string;
  deliveryLabel: string;
  returnsLabel: string;
  paymentLabel: string;
  category?: {
    name: string;
    slug: string;
    parent?: { name: string; slug: string };
  };
  gallery: PublicProductGalleryItem[];
  variants: PublicProductVariant[];
  specs: ProductSpec[];
  aboutItems: string[];
};

export type FeaturedSidebarProduct = {
  id: string;
  title: string;
  image: string;
  href: string;
  rating: number;
  reviews: string;
  currencyCode: string;
  priceWhole: string;
  priceDecimal: string;
  extraPrice?: string;
  shipping: string;
  delivery?: string;
  price: number;
};

export type PublicOfferTargetProduct = {
  id: string;
  slug: string;
  title: string;
  image: string;
  shortDescription: string;
  href: string;
};

export type CategoryListingProduct = {
  id: string;
  name: string;
  image: string;
  href: string;
  shortDesc: string;
  price: number;
  rating?: number;
  oldPrice?: number;
};

// ─── functions ────────────────────────────────────────────────

export async function getPublicProductBySlug(
  slug: string
): Promise<PublicProductPageData | null> {
  try {
    const data = await apiFetch<{ product: PublicProductPageData }>(
      `/products/${encodeURIComponent(slug)}`
    );
    return data.product ?? null;
  } catch {
    return null;
  }
}

export async function getRelatedProductsForProduct(
  productId: string,
  _categoryId?: string | null,
  _limit = 8
): Promise<RelatedProduct[]> {
  try {
    // We look up the slug first from the product list — or pass slug directly.
    // The backend route is /products/{slug}/related.
    // Since we only have the ID here, we'll hit the admin route to get the slug.
    const detail = await apiFetch<{ product: { slug: string } }>(
      `/admin/products/${productId}`
    ).catch(() => null);

    if (!detail?.product?.slug) return [];

    const data = await apiFetch<{ products: RelatedProduct[] }>(
      `/products/${encodeURIComponent(detail.product.slug)}/related`
    );
    return data.products ?? [];
  } catch {
    return [];
  }
}

export async function getFeaturedSidebarProducts(
  limit = 5
): Promise<FeaturedSidebarProduct[]> {
  try {
    const data = await apiFetch<{ products: FeaturedSidebarProduct[] }>(
      `/products/featured`
    );
    return (data.products ?? []).slice(0, limit);
  } catch {
    return [];
  }
}

export async function getOfferTargetProductsBySlugs(
  slugs: string[]
): Promise<PublicOfferTargetProduct[]> {
  const unique = Array.from(new Set(slugs.filter(Boolean)));
  if (!unique.length) return [];

  const params = unique.map((s) => `slugs[]=${encodeURIComponent(s)}`).join("&");

  try {
    const data = await apiFetch<{ products: PublicOfferTargetProduct[] }>(
      `/products/offer-targets?${params}`
    );
    return data.products ?? [];
  } catch {
    return [];
  }
}

export async function getProductsByCategorySlug(slug: string) {
  try {
    const data = await apiFetch<{
      categoryId: string;
      slug: string;
      title: string;
      description: string;
      image: string;
      rootCategory: string;
      products: CategoryListingProduct[];
    }>(`/categories/${encodeURIComponent(slug)}/products`);
    return data;
  } catch {
    return null;
  }
}

export async function getSearchSuggestionsByCategory(
  categoryId: string,
  limit = 8
): Promise<{ id: string; title: string; image: string; href: string }[]> {
  try {
    const data = await apiFetch<{ products: { id: string; name: string; image: string; href: string }[] }>(
      `/categories/by-id/${categoryId}/products`
    );
    return (data.products ?? []).slice(0, limit).map((p) => ({
      id: p.id, title: p.name, image: p.image, href: p.href,
    }));
  } catch {
    return [];
  }
}

export async function getSameCategoryProductsForSearch(
  _productId: string,
  _categoryId?: string | null,
  _limit = 8
): Promise<{ id: string; title: string; image: string; href: string }[]> {
  return [];
}

export async function getFrequentlyViewedProducts(
  productId: string,
  categoryId?: string | null,
  limit = 8
): Promise<RelatedProduct[]> {
  return getRelatedProductsForProduct(productId, categoryId, limit);
}

// ─── home "featured category" loaders ─────────────────────────
//
// These populate the 3 cards under the hero (CategoryTilesSection).
// We locate a matching category by title/slug via the public frontend-data
// endpoint, then load its products through /categories/{slug}/products.

type FeatureCategory = {
  title: string;
  slug: string;
  products: {
    id: string;
    name: string;
    image: string;
    href: string;
    price: number;
    isFeatured?: boolean;
  }[];
};

async function findFirstActiveCategoryBy(
  predicates: ((c: {
    title: string;
    slug: string;
    rootCategory?: string;
    isActive: boolean;
  }) => boolean)[]
): Promise<{ title: string; slug: string } | null> {
  try {
    const data = await apiFetch<{
      categories: {
        title: string;
        slug: string;
        rootCategory?: string;
        isActive: boolean;
      }[];
    }>(`/frontend-data`);

    const list = (data.categories ?? []).filter((c) => c.isActive !== false && c.slug);
    for (const predicate of predicates) {
      const hit = list.find(predicate);
      if (hit) return { title: hit.title, slug: hit.slug };
    }
  } catch {
    /* ignore */
  }
  return null;
}

async function loadCategoryFeatureByCandidates(
  predicates: ((c: {
    title: string;
    slug: string;
    rootCategory?: string;
    isActive: boolean;
  }) => boolean)[]
): Promise<FeatureCategory | null> {
  const match = await findFirstActiveCategoryBy(predicates);
  if (!match) return null;

  const data = await getProductsByCategorySlug(match.slug);
  if (!data || !data.products?.length) return null;

  return {
    title: data.title || match.title,
    slug: data.slug || match.slug,
    products: data.products.map((p) => ({
      id: p.id,
      name: p.name,
      image: p.image,
      href: p.href,
      price: p.price,
      // CategoryListingProduct doesn't include isFeatured; leave undefined so
      // the UI falls back to the regular product list.
    })),
  };
}

export async function getSparePartsCategoryFeature(): Promise<FeatureCategory | null> {
  const lc = (s?: string) => (s ?? "").toLowerCase();
  return loadCategoryFeatureByCandidates([
    (c) => /spare\s*parts?/i.test(c.title) || /spare[-_]?parts?/i.test(c.slug),
    (c) => /tv\s*parts?/i.test(c.title) || /tv[-_]?parts?/i.test(c.slug),
    (c) => /parts?/i.test(c.title) || /parts?/.test(lc(c.slug)),
    (c) => lc(c.rootCategory).includes("spare") || lc(c.rootCategory).includes("parts"),
  ]);
}

export async function getApplianceCategoryFeature(): Promise<FeatureCategory | null> {
  const lc = (s?: string) => (s ?? "").toLowerCase();
  return loadCategoryFeatureByCandidates([
    (c) => /appliance/i.test(c.title) || /appliance/.test(lc(c.slug)),
    (c) => lc(c.rootCategory).includes("appliance"),
    (c) => /home/i.test(c.title) && /good|ware|appliance/i.test(c.title),
  ]);
}
