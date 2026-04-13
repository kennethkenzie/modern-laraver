import { notFound } from "next/navigation";
import NavBar from "@/components/NavBar";
import ProductDetailsClient from "@/components/ProductDetailsClient";
import RelatedProductsCarousel from "@/components/RelatedProductsCarousel";
import Footer from "@/components/Footer";
import {
  getFrequentlyViewedProducts,
  getPublicProductBySlug,
  getRelatedProductsForProduct,
  getSameCategoryProductsForSearch,
} from "@/lib/products-public";
import { readFrontendDataFromPrisma } from "@/lib/site-settings";
import { mergeFrontendData } from "@/lib/frontend-data-merge";

export default async function ProductDetailsPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const product = await getPublicProductBySlug(slug);

  if (!product) {
    notFound();
  }

  const [frontendDataRaw, relatedProducts, frequentlyViewed, searchSuggestions] = await Promise.all([
    readFrontendDataFromPrisma().catch(() => null),
    getRelatedProductsForProduct(product.id, product.categoryId),
    getFrequentlyViewedProducts(product.id, product.categoryId),
    getSameCategoryProductsForSearch(product.id, product.categoryId),
  ]);

  const frontendData = frontendDataRaw ?? mergeFrontendData({});

  const relatedSection = frontendData.relatedProducts;
  const relatedPreview = relatedProducts[0]
    ? {
        title: relatedProducts[0].title,
        image: relatedProducts[0].image,
        href: relatedProducts[0].href,
        price: relatedProducts[0].price,
      }
    : undefined;
  return (
    <>
      <NavBar
        searchSuggestions={searchSuggestions}
        searchContextLabel={product.category?.name}
        initialData={frontendData}
      />
      <ProductDetailsClient
        product={product}
        relatedPreview={relatedPreview}
      />
      <RelatedProductsCarousel
        products={relatedProducts}
        title={relatedSection?.title}
        sponsoredLabel={relatedSection?.sponsoredLabel}
        pageLabel={relatedProducts.length > 0 ? "Page 1 of 1" : relatedSection?.pageLabel}
      />
      <RelatedProductsCarousel
        products={frequentlyViewed}
        title="Frequently viewed"
        sponsoredLabel={null}
        pageLabel={null}
      />
      <Footer />
    </>
  );
}
