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

export async function getApplianceCategoryFeature(): Promise<{
  title: string;
  slug: string;
  products: { id: string; name: string; image: string; href: string; price: number; isFeatured?: boolean }[];
} | null> {
  return null;
}

export async function getSparePartsCategoryFeature(): Promise<{
  title: string;
  slug: string;
  products: { id: string; name: string; image: string; href: string; price: number; isFeatured?: boolean }[];
} | null> {
  return null;
}
