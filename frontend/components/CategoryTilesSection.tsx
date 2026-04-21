"use client";

import { useEffect, useMemo, useState } from "react";
import Link from "next/link";
import SafeImage from "@/components/SafeImage";
import { useFrontendData } from "@/lib/use-frontend-data";
import type { CategoryTile } from "@/lib/frontend-data";

type SparePartsFeature = {
  title: string;
  href: string;
  products: Array<{
    id: string;
    name: string;
    image: string;
    href: string;
  }>;
  featuredProducts: Array<{
    id: string;
    name: string;
    image: string;
    href: string;
  }>;
};

type ApplianceFeature = {
  title: string;
  href: string;
  products: Array<{
    id: string;
    name: string;
    image: string;
    href: string;
  }>;
};

function pickRandomTiles(
  products: SparePartsFeature["products"],
  count = 4
): CategoryTile[] {
  const shuffled = [...products].sort(() => Math.random() - 0.5);
  return shuffled.slice(0, count).map((product) => ({
    label: product.name,
    image: product.image,
    href: product.href,
  }));
}

function pickInitialTiles(
  products: SparePartsFeature["products"],
  count = 4
): CategoryTile[] {
  return products.slice(0, count).map((product) => ({
    label: product.name,
    image: product.image,
    href: product.href,
  }));
}

export default function CategoryTilesSection({
  sparePartsFeature,
  applianceFeature,
}: {
  sparePartsFeature?: SparePartsFeature;
  applianceFeature?: ApplianceFeature;
}) {
  const { data } = useFrontendData();
  const cards = data.categoryTiles.cards;
  const [sparePartsTiles, setSparePartsTiles] = useState<CategoryTile[]>(() =>
    sparePartsFeature ? pickInitialTiles(sparePartsFeature.products) : []
  );
  const featuredSparePartsTiles = useMemo(
    () =>
      sparePartsFeature
        ? sparePartsFeature.featuredProducts.map((product) => ({
            label: product.name,
            image: product.image,
            href: product.href,
          }))
        : [],
    [sparePartsFeature]
  );

  useEffect(() => {
    if (!sparePartsFeature || sparePartsFeature.products.length === 0) {
      setSparePartsTiles([]);
      return;
    }

    setSparePartsTiles(pickRandomTiles(sparePartsFeature.products));
    const interval = window.setInterval(() => {
      setSparePartsTiles(pickRandomTiles(sparePartsFeature.products));
    }, 7000);

    return () => window.clearInterval(interval);
  }, [sparePartsFeature]);

  const [applianceTiles, setApplianceTiles] = useState<CategoryTile[]>(() =>
    applianceFeature ? pickInitialTiles(applianceFeature.products) : []
  );

  useEffect(() => {
    if (!applianceFeature || applianceFeature.products.length === 0) {
      setApplianceTiles([]);
      return;
    }

    setApplianceTiles(pickRandomTiles(applianceFeature.products));
    const interval = window.setInterval(() => {
      setApplianceTiles(pickRandomTiles(applianceFeature.products));
    }, 7000);

    return () => window.clearInterval(interval);
  }, [applianceFeature]);

  const displayCards = useMemo(() => {
    if (cards.length < 2) {
      return cards;
    }

    // Prefer featured spare-parts tiles; fall back to the rotating general
    // spare-parts tiles so the "Top categories in Spare parts" card is never
    // empty when we actually have spare-parts products.
    const topSparePartsTiles =
      featuredSparePartsTiles.length > 0
        ? featuredSparePartsTiles
        : sparePartsTiles;

    return cards.map((card, idx) => {
      if (idx === 0) {
        // Only override when we actually have spare-parts products to show.
        if (topSparePartsTiles.length === 0) return card;
        return {
          ...card,
          title: "Top categories in Spare parts",
          tiles: topSparePartsTiles,
          cta: {
            label: "Shop Spare parts",
            href: sparePartsFeature?.href || card.cta.href,
          },
        };
      }
      if (idx === 1 && sparePartsFeature && sparePartsTiles.length > 0) {
        return {
          ...card,
          title: `More from ${sparePartsFeature.title}`,
          tiles: sparePartsTiles,
          cta: {
            label: `Shop ${sparePartsFeature.title}`,
            href: sparePartsFeature.href,
          },
        };
      }
      if (idx === 2 && applianceFeature && applianceTiles.length > 0) {
        return {
          ...card,
          title: "Home Appliance",
          tiles: applianceTiles,
          cta: {
            label: "Shop appliances",
            href: applianceFeature.href,
          },
        };
      }
      return card;
    });
  }, [cards, featuredSparePartsTiles, sparePartsFeature, sparePartsTiles, applianceFeature, applianceTiles]);

  return (
    <section className="w-full bg-white">
      <div className="mx-auto w-[98%] px-4 py-8">
        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
          {displayCards.map((card, idx) => (
            <div
              key={idx}
              className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"
            >
              <h3 className="text-[20px] font-extrabold leading-snug text-gray-900">
                {card.title}
              </h3>

              {card.tiles.length > 0 ? (
                <div className="mt-4 grid grid-cols-2 gap-4">
                  {card.tiles.map((t) => (
                    <Link
                      key={t.label}
                      href={t.href}
                      className="group block"
                      aria-label={t.label}
                    >
                      <div className="relative aspect-[4/3] overflow-hidden rounded-xl bg-gray-100">
                        <SafeImage
                          src={t.image}
                          alt={t.label}
                          className="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.04]"
                        />
                      </div>
                      <div className="mt-2 text-[13px] text-gray-800 group-hover:text-[#ff6a00]">
                        {t.label}
                      </div>
                    </Link>
                  ))}
                </div>
              ) : (
                <div className="mt-4 rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-10 text-center text-[13px] text-gray-500">
                  No products to show yet.
                </div>
              )}

              <Link
                href={card.cta.href}
                className="mt-6 inline-block text-[13px] font-semibold text-[#0b63ce] hover:underline"
              >
                {card.cta.label}
              </Link>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
