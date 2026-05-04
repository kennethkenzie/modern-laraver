"use client";

import {
  startTransition,
  useEffect,
  useRef,
  useState,
  type Dispatch,
  type MutableRefObject,
  type SetStateAction,
} from "react";
import Link from "next/link";
import { ShoppingBag, Star } from "lucide-react";
import SafeImage from "@/components/SafeImage";
import { addToCart } from "@/lib/cart";
import { normalizeMediaUrl } from "@/lib/media";
import type { LatestProduct } from "@/lib/frontend-data";
import WishlistButton from "@/components/WishlistButton";

type ProductFeedItem = LatestProduct & {
  renderKey: string;
  isFresh: boolean;
};

type LatestProductsSectionProps = {
  title: string;
  ctaHref: string;
  ctaLabel: string;
  products: LatestProduct[];
};

const PAGE_SIZE = 10;

function formatUGX(n: number) {
  return `UGX ${n.toLocaleString("en-US")}`;
}

function Rating({ value = 0 }: { value?: number }) {
  const full = Math.round(value);
  return (
    <div className="flex items-center gap-1 text-gray-300">
      {Array.from({ length: 5 }).map((_, i) => (
        <Star
          key={i}
          size={16}
          className={i < full ? "fill-[#f59e0b] text-[#f59e0b]" : ""}
        />
      ))}
    </div>
  );
}

function toFeedItems(products: LatestProduct[], startIndex: number) {
  return products.map((product, index) => ({
    ...product,
    renderKey: `${product.id}-${startIndex + index}`,
    isFresh: startIndex > 0,
  }));
}

export default function LatestProductsSection({
  title,
  ctaHref,
  ctaLabel,
  products,
}: LatestProductsSectionProps) {
  const [items, setItems] = useState<ProductFeedItem[]>(() => toFeedItems(products, 0));
  const [offset, setOffset] = useState(products.length);
  const [isLoading, setIsLoading] = useState(false);
  const [requestCount, setRequestCount] = useState(1);
  const [renderIndex, setRenderIndex] = useState(products.length);
  const sentinelRef = useRef<HTMLDivElement | null>(null);
  const observerRef = useRef<IntersectionObserver | null>(null);
  const isLoadingRef = useRef(false);
  const offsetRef = useRef(products.length);
  const renderIndexRef = useRef(products.length);

  useEffect(() => {
    const nextItems = toFeedItems(products, 0);
    setItems(nextItems);
    setOffset(products.length);
    setRequestCount(1);
    setRenderIndex(products.length);
    offsetRef.current = products.length;
    renderIndexRef.current = products.length;
    isLoadingRef.current = false;
    setIsLoading(false);
  }, [products]);

  useEffect(() => {
    if (!sentinelRef.current) {
      return;
    }

    observerRef.current?.disconnect();
    observerRef.current = new IntersectionObserver(
      (entries) => {
        const firstEntry = entries[0];
        if (firstEntry?.isIntersecting) {
          void fetchMore(offsetRef, renderIndexRef, isLoadingRef, {
            setItems,
            setOffset,
            setIsLoading,
            setRequestCount,
            setRenderIndex,
          });
        }
      },
      {
        rootMargin: "900px 0px",
        threshold: 0.01,
      }
    );

    observerRef.current.observe(sentinelRef.current);
    return () => observerRef.current?.disconnect();
  }, []);

  useEffect(() => {
    if (items.length === 0 || !items.some((item) => item.isFresh)) {
      return;
    }

    const timeout = window.setTimeout(() => {
      setItems((current) =>
        current.map((item) => (item.isFresh ? { ...item, isFresh: false } : item))
      );
    }, 650);

    return () => window.clearTimeout(timeout);
  }, [items]);

  return (
    <section className="w-full bg-white">
      <div className="mx-auto w-[98%] px-4 py-8">
        <div className="mb-5 flex items-end justify-between gap-4">
          <div>
            <h2 className="text-[24px] font-semibold tracking-[-0.02em] text-gray-900">{title}</h2>
            <p className="mt-1 text-[14px] text-gray-500">
              Fresh catalog picks that keep loading as you browse.
            </p>
          </div>
          <Link
            href={ctaHref}
            className="rounded-full border border-gray-200 px-4 py-2 text-[13px] font-semibold text-[#0b63ce] transition hover:border-[#0b63ce]/30 hover:bg-[#0b63ce]/5"
          >
            {ctaLabel}
          </Link>
        </div>

        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
          {items.map((product, i) => (
            <ProductCard key={product.renderKey} product={product} priority={i < 4} />
          ))}
          {isLoading ? Array.from({ length: Math.min(PAGE_SIZE, 8) }).map((_, index) => <ProductSkeleton key={`skeleton-${requestCount}-${index}`} />) : null}
        </div>

        <div ref={sentinelRef} className="h-6 w-full" aria-hidden="true" />
      </div>
    </section>
  );
}

async function fetchMore(
  offsetRef: MutableRefObject<number>,
  renderIndexRef: MutableRefObject<number>,
  isLoadingRef: MutableRefObject<boolean>,
  controls: {
    setItems: Dispatch<SetStateAction<ProductFeedItem[]>>;
    setOffset: Dispatch<SetStateAction<number>>;
    setIsLoading: Dispatch<SetStateAction<boolean>>;
    setRequestCount: Dispatch<SetStateAction<number>>;
    setRenderIndex: Dispatch<SetStateAction<number>>;
  }
) {
  if (isLoadingRef.current) {
    return;
  }

  isLoadingRef.current = true;
  controls.setIsLoading(true);
  controls.setRequestCount((count) => count + 1);

  try {
    const response = await fetch(`/api/products/latest?offset=${offsetRef.current}&limit=${PAGE_SIZE}`, {
      cache: "no-store",
    });

    if (!response.ok) {
      throw new Error("Failed to load more products.");
    }

    const payload = (await response.json()) as {
      products?: LatestProduct[];
      nextOffset?: number;
    };
    const incoming = Array.isArray(payload.products) ? payload.products : [];

    if (incoming.length === 0) {
      return;
    }

    const nextStart = renderIndexRef.current;
    const appendedItems = toFeedItems(incoming, nextStart);

    startTransition(() => {
      controls.setItems((current) => [...current, ...appendedItems]);
      controls.setOffset(payload.nextOffset ?? offsetRef.current + incoming.length);
      controls.setRenderIndex(nextStart + incoming.length);
    });

    offsetRef.current = payload.nextOffset ?? offsetRef.current + incoming.length;
    renderIndexRef.current = nextStart + incoming.length;
  } catch (error) {
    console.error("Failed to append products in the home feed.", error);
  } finally {
    isLoadingRef.current = false;
    controls.setIsLoading(false);
  }
}

function ProductCard({ product: p, priority }: { product: ProductFeedItem; priority?: boolean }) {
  return (
    <article
      className={[
        "group rounded-[22px] border border-gray-200/80 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_22px_50px_rgba(15,23,42,0.1)]",
        p.isFresh ? "latest-product-enter" : "",
      ].join(" ")}
    >
      <div className="relative aspect-square overflow-hidden rounded-t-[22px]">
        <WishlistButton
          item={{
            id: p.id,
            name: p.name,
            price: p.price,
            image: normalizeMediaUrl(p.image),
            href: p.href,
          }}
          className="absolute right-3 top-3 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-gray-700 shadow-sm transition hover:bg-white"
        />

        <Link href={p.href} aria-label={p.name} className="absolute inset-0">
          <SafeImage
            src={p.image}
            alt={p.name}
            fill
            priority={priority}
            sizes="(max-width: 640px) 100vw, (max-width: 1280px) 50vw, (max-width: 1536px) 33vw, 25vw"
            className="object-contain transition-transform duration-500 group-hover:scale-[1.04]"
          />
        </Link>
      </div>

      <div className="flex min-h-[230px] flex-col p-5">
        <Link
          href={p.href}
          className="line-clamp-2 text-[17px] font-semibold leading-6 text-gray-900 transition hover:text-[#ff6a00]"
          title={p.name}
        >
          {p.name}
        </Link>

        <p className="mt-2 line-clamp-2 text-[13px] leading-6 text-gray-600">
          {p.shortDesc}
        </p>

        <div className="mt-5 flex items-center gap-3">
          <div className="text-[24px] font-extrabold tracking-[-0.02em] text-[#16a34a]">
            {formatUGX(p.price)}
          </div>
        </div>

        <div className="mt-3">
          <Rating value={p.rating} />
        </div>

        <button
          onClick={() =>
            addToCart({
              id: p.id,
              name: p.name,
              price: p.price,
              image: normalizeMediaUrl(p.image),
              href: p.href,
            })
          }
          className="mt-auto inline-flex items-center justify-center gap-2 rounded-full bg-[#1f2937] px-5 py-3 text-[13px] font-semibold tracking-[0.04em] text-white transition hover:bg-black"
        >
          <ShoppingBag size={16} />
          ADD TO CART
        </button>
      </div>
    </article>
  );
}

function ProductSkeleton() {
  return (
    <div className="overflow-hidden rounded-[22px] border border-gray-200/80 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.05)]">
      <div className="aspect-square w-full animate-pulse bg-[linear-gradient(90deg,#f3f4f6_0%,#e5e7eb_50%,#f3f4f6_100%)]" />
      <div className="space-y-4 p-5">
        <div className="h-5 w-3/4 animate-pulse rounded-full bg-gray-200" />
        <div className="h-4 w-full animate-pulse rounded-full bg-gray-100" />
        <div className="h-4 w-2/3 animate-pulse rounded-full bg-gray-100" />
        <div className="h-6 w-1/2 animate-pulse rounded-full bg-gray-200" />
        <div className="h-4 w-24 animate-pulse rounded-full bg-gray-100" />
        <div className="h-11 w-full animate-pulse rounded-full bg-gray-200" />
      </div>
    </div>
  );
}
