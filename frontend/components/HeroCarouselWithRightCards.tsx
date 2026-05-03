"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import {
  ArrowLeft,
  ArrowRight,
  ChevronLeft,
  ChevronRight,
  BadgeCheck,
} from "lucide-react";
import SafeImage from "@/components/SafeImage";
import { useFrontendData } from "@/lib/use-frontend-data";
import type { FrontendData } from "@/lib/frontend-data";

export default function HeroCarouselWithRightCards({ initialData }: { initialData?: FrontendData }) {
  const { data } = useFrontendData(initialData);
  const slides = data.hero.slides;
  const rightCards = data.hero.sideCards;
  const featuredOffers = data.offers.filter((offer) => offer.isActive && offer.isFeatured);
  const activeOffers = data.offers.filter((offer) => offer.isActive && !offer.isFeatured);
  const heroOffers = [...featuredOffers, ...activeOffers].slice(0, 2);

  const [active, setActive] = useState(0);
  const [paused, setPaused] = useState(false);

  const total = slides.length;
  const activeIndex = total > 0 ? active % total : 0;

  const go = (idx: number) => {
    if (!total) return;
    setActive((idx + total) % total);
  };

  useEffect(() => {
    if (!total || paused) return;
    const t = setInterval(() => setActive((p) => (p + 1) % total), 5000);
    return () => clearInterval(t);
  }, [paused, total]);

  if (!slides.length && !rightCards.length && !heroOffers.length) return null;

  return (
    <section className="w-full bg-white">
      <div className="mx-auto w-full max-w-[1400px] px-2 py-3 sm:px-4 sm:py-5">

        {/* ── Layout: stack on mobile, side-by-side on lg ── */}
        <div className="flex flex-col gap-3 lg:grid lg:grid-cols-12 lg:gap-4">

          {/* ── Main slider ── */}
          <div
            className="w-full overflow-hidden rounded-xl border border-gray-200 shadow-sm lg:col-span-8"
            onMouseEnter={() => setPaused(true)}
            onMouseLeave={() => setPaused(false)}
          >
            {/*
              padding-bottom trick gives a true aspect-ratio box that works on
              every screen size. clamp keeps it from being too short on mobile
              or too tall on large monitors.
            */}
            <div
              className="relative w-full"
              style={{ paddingBottom: "clamp(200px, 52%, 520px)" }}
            >
              {/* Slide strip */}
              <div
                className="absolute inset-0 flex transition-transform duration-500 ease-out"
                style={{ transform: `translateX(-${activeIndex * 100}%)` }}
              >
                {slides.map((s) => {
                  const slideHref = getSafeHref(s.ctaHref);
                  return (
                    <div key={s.id} className="absolute inset-0 h-full min-w-full flex-shrink-0"
                      style={{ left: `${slides.indexOf(s) * 100}%` }}>
                      <SafeImage
                        src={s.image}
                        alt={s.title || "Slide"}
                        className="absolute inset-0 h-full w-full object-cover object-center"
                      />
                      {/* gradient overlay — stronger at bottom for text legibility */}
                      <div className="absolute inset-0 bg-gradient-to-t from-black/65 via-black/20 to-transparent" />

                      {/* Text content */}
                      <div className="relative z-[1] flex h-full flex-col justify-end p-4 sm:p-6 md:p-8">
                        <div className="max-w-[640px]">
                          <h2 className="font-display text-[17px] font-bold leading-tight text-white
                                         sm:text-[24px] md:text-[34px] lg:text-[40px]">
                            {s.title}
                          </h2>
                          <p className="mt-1 max-w-[520px] text-[11px] leading-4 text-white/85
                                        sm:mt-2 sm:text-[13px] sm:leading-5
                                        md:text-[15px] md:leading-6">
                            {s.description}
                          </p>
                        </div>

                        <div className="mt-3 flex items-center gap-3 sm:mt-5">
                          {slideHref ? (
                            <Link
                              href={slideHref}
                              className="inline-flex items-center gap-1.5 rounded-lg border border-white/50
                                         bg-white px-3 py-2 text-[12px] font-bold text-gray-900 shadow-sm
                                         hover:bg-gray-100 sm:gap-2 sm:px-5 sm:py-3 sm:text-[14px]"
                            >
                              {s.ctaLabel || "Shop now"}
                              <ArrowRight size={14} className="sm:hidden" />
                              <ArrowRight size={17} className="hidden sm:block" />
                            </Link>
                          ) : null}
                        </div>

                        {/* Dot indicators */}
                        <div className="mt-3 flex items-center gap-2 sm:mt-5">
                          {slides.map((_, i) => (
                            <button
                              key={i}
                              onClick={() => go(i)}
                              className={[
                                "h-1.5 rounded-full transition-all sm:h-2",
                                i === activeIndex
                                  ? "w-4 bg-[#ff6a00] sm:w-6"
                                  : "w-2.5 bg-white/55 hover:bg-white sm:w-4",
                              ].join(" ")}
                              aria-label={`Go to slide ${i + 1}`}
                            />
                          ))}
                        </div>
                      </div>

                      {/* Verified badge — desktop only */}
                      <div className="absolute bottom-5 right-5 z-[2] hidden rounded-xl bg-white/90
                                      p-2.5 shadow-sm backdrop-blur md:block">
                        <BadgeCheck size={15} className="text-[#f59e0b]" />
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* Desktop prev/next arrows */}
              <div className="absolute right-4 top-1/2 z-10 hidden -translate-y-1/2 flex-col
                              overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm md:flex">
                <button onClick={() => go(active + 1)}
                  className="flex h-10 w-10 items-center justify-center hover:bg-gray-50"
                  aria-label="Next slide">
                  <ChevronRight size={16} />
                </button>
                <div className="h-px w-full bg-gray-200" />
                <button onClick={() => go(active - 1)}
                  className="flex h-10 w-10 items-center justify-center hover:bg-gray-50"
                  aria-label="Previous slide">
                  <ChevronLeft size={16} />
                </button>
              </div>

              {/* Mobile prev/next arrows */}
              <div className="absolute bottom-4 right-4 z-10 flex gap-2 md:hidden">
                <button onClick={() => go(active - 1)}
                  className="inline-flex h-8 w-8 items-center justify-center rounded-lg
                             border border-gray-200 bg-white shadow-sm active:scale-95"
                  aria-label="Previous slide">
                  <ArrowLeft size={14} />
                </button>
                <button onClick={() => go(active + 1)}
                  className="inline-flex h-8 w-8 items-center justify-center rounded-lg
                             border border-gray-200 bg-white shadow-sm active:scale-95"
                  aria-label="Next slide">
                  <ArrowRight size={14} />
                </button>
              </div>
            </div>
          </div>

          {/* ── Right cards (offers or side cards) ── */}
          <div className="grid grid-cols-2 gap-3 lg:col-span-4 lg:flex lg:flex-col lg:gap-3">
            {heroOffers.length > 0
              ? heroOffers.map((offer, index) => {
                  const hasTargetImage =
                    (offer.targetType === "product" || offer.targetType === "category") &&
                    offer.targetImage;

                  if (hasTargetImage) {
                    return (
                      <Link
                        key={offer.id}
                        href={getOfferHref(offer)}
                        className="group relative overflow-hidden rounded-xl border border-[#d7dde5]
                                   bg-white shadow-sm transition hover:shadow-md"
                      >
                        <div className="flex min-h-[140px] flex-col sm:grid sm:grid-cols-12 sm:min-h-[160px] lg:min-h-[180px]">
                          <div className="flex items-center justify-center bg-[linear-gradient(180deg,#f8fafc,#eef3f8)]
                                          p-3 sm:col-span-5">
                            <SafeImage
                              src={offer.targetImage}
                              alt={offer.targetTitle || offer.title}
                              className="h-[90px] w-full max-w-[140px] object-contain
                                         transition-transform duration-300 group-hover:scale-[1.04]
                                         sm:h-[120px] lg:h-[140px]"
                            />
                          </div>
                          <div className="flex flex-col justify-between p-3 sm:col-span-7 sm:p-4">
                            <div className="flex flex-wrap items-start gap-1.5">
                              <span className="rounded-full bg-[#cc0c39] px-2 py-0.5 text-[9px]
                                               font-black uppercase tracking-[0.18em] text-white sm:text-[10px]">
                                {offer.badgeText || `Offer ${index + 1}`}
                              </span>
                              <span className="rounded-full bg-[#fff7ed] px-2 py-0.5 text-[9px]
                                               font-bold text-[#c2410c] sm:text-[11px]">
                                {formatOfferValue(offer)}
                              </span>
                            </div>
                            <div className="mt-1.5">
                              <p className="text-[9px] font-semibold uppercase tracking-[0.18em] text-[#6b7280] sm:text-[10px]">
                                {formatOfferTarget(offer)}
                              </p>
                              <p className="mt-1 line-clamp-2 text-[13px] font-bold leading-tight
                                            text-[#111827] sm:text-[15px] lg:text-[17px]">
                                {offer.headline || offer.targetTitle || offer.title}
                              </p>
                            </div>
                            <div className="mt-2 flex justify-end">
                              <span className="inline-flex items-center gap-1 rounded-full bg-[#ffd814]
                                               px-2 py-1 text-[10px] font-bold text-[#111827]
                                               transition group-hover:bg-[#f7ca00] sm:text-[11px]">
                                {offer.targetType === "product" ? "View" : "Shop"}
                                <ArrowRight size={11} />
                              </span>
                            </div>
                          </div>
                        </div>
                        <div className="absolute inset-x-0 bottom-0 h-[3px] bg-[#f6c400]" />
                      </Link>
                    );
                  }

                  return (
                    <Link
                      key={offer.id}
                      href={getOfferHref(offer)}
                      className="group relative overflow-hidden rounded-xl border border-[#d7dde5]
                                 bg-[linear-gradient(135deg,#f8fafc,#f1f5f9)] shadow-sm transition hover:shadow-md"
                    >
                      {offer.bannerImage ? (
                        <>
                          <SafeImage src={offer.bannerImage} alt={offer.title}
                            className="absolute inset-0 h-full w-full object-cover" />
                          <div className="absolute inset-0 bg-white/10" />
                        </>
                      ) : null}
                      <div className="relative flex min-h-[140px] flex-col justify-between gap-2
                                      p-3 sm:min-h-[160px] sm:p-4 lg:min-h-[180px]">
                        <div className="flex flex-wrap items-start gap-1.5">
                          <span className="rounded-full bg-gray-100 px-2 py-0.5 text-[9px]
                                           font-black uppercase tracking-[0.2em] text-gray-500 sm:text-[10px]">
                            {offer.badgeText || `Offer ${index + 1}`}
                          </span>
                          <span className="rounded-full border border-gray-200 bg-white/10 px-2 py-0.5
                                           text-[9px] font-bold text-gray-700 sm:text-[11px]">
                            {formatOfferValue(offer)}
                          </span>
                        </div>
                        <div>
                          <p className="text-[9px] font-semibold uppercase tracking-[0.18em] text-[#114f8f] sm:text-[10px]">
                            {formatOfferTarget(offer)}
                          </p>
                          <p className="mt-1 line-clamp-2 text-[14px] font-bold leading-tight
                                        text-gray-900 sm:text-[17px] lg:text-[19px]">
                            {offer.headline || offer.title}
                          </p>
                          <p className="mt-1 line-clamp-2 text-[10px] leading-4 text-gray-600 sm:text-[12px]">
                            {offer.description || "Shop the latest deal now before it expires."}
                          </p>
                        </div>
                        <div className="flex justify-end">
                          <span className="inline-flex items-center gap-1 rounded-full bg-[#ffd814]
                                           px-2 py-1 text-[10px] font-bold text-[#111827]
                                           transition group-hover:bg-[#f7ca00] sm:text-[11px]">
                            Shop offer <ArrowRight size={11} />
                          </span>
                        </div>
                      </div>
                      <div className="absolute inset-x-0 bottom-0 h-[3px] bg-[#f6c400]" />
                    </Link>
                  );
                })
              : rightCards.map((c) => (
                  <Link
                    key={c.id}
                    href={getSafeHref(c.href) ?? "/products"}
                    className="group relative overflow-hidden rounded-xl border border-[#d7dde5]
                               bg-white shadow-sm transition hover:shadow-md"
                  >
                    <div className="flex min-h-[140px] flex-col sm:grid sm:grid-cols-12
                                    sm:min-h-[160px] lg:min-h-[180px]">
                      <div className="flex items-center justify-center rounded-t-xl
                                      bg-[linear-gradient(180deg,#f8fafc,#eef3f8)]
                                      p-3 sm:col-span-5 sm:rounded-l-xl sm:rounded-t-none">
                        <SafeImage
                          src={c.image}
                          alt={c.title}
                          className="h-[80px] w-full max-w-[120px] rounded-lg object-cover shadow-sm
                                     transition-transform duration-300 group-hover:scale-[1.03]
                                     sm:h-[100px] sm:w-[130px] lg:h-[120px]"
                        />
                      </div>
                      <div className="flex flex-col justify-center p-3 sm:col-span-7 sm:p-4">
                        <p className="text-[9px] font-semibold uppercase tracking-[0.16em] text-gray-500 sm:text-[11px]">
                          {c.eyebrow}
                        </p>
                        <p className="mt-1 line-clamp-2 text-[13px] font-bold leading-tight
                                      text-gray-900 sm:text-[16px] lg:text-[18px]">
                          {c.title}
                        </p>
                        <span className="mt-2 inline-flex w-fit items-center gap-1 rounded-full
                                         bg-[#ffd814] px-2 py-1 text-[10px] font-bold text-[#111827]
                                         transition group-hover:bg-[#f7ca00] sm:text-[11px]">
                          Shop deals <ArrowRight size={11} />
                        </span>
                      </div>
                    </div>
                    <div className="absolute inset-x-0 bottom-0 h-[3px] bg-transparent
                                    transition-colors duration-300 group-hover:bg-[#f6c400]" />
                  </Link>
                ))}
          </div>
        </div>
      </div>
    </section>
  );
}

function getSafeHref(value: unknown) {
  if (typeof value !== "string") return null;
  const href = value.trim();
  return href || null;
}

function getOfferHref(offer: { code: string; targetType: string; targetValue: string }) {
  if (offer.targetType === "category" && offer.targetValue) return `/category/${offer.targetValue}`;
  if (offer.targetType === "product" && offer.targetValue) return `/product/${offer.targetValue}`;
  if (offer.targetType === "pickup") return "/checkout";
  return offer.code ? `/products?offer=${encodeURIComponent(offer.code)}` : "/products";
}

function formatOfferValue(offer: { discountType: string; discountValue: number }) {
  if (offer.discountType === "free_shipping") return "Free shipping";
  if (offer.discountType === "fixed") return `UGX ${offer.discountValue} off`;
  return `${offer.discountValue}% off`;
}

function formatOfferTarget(offer: { targetType: string }) {
  if (offer.targetType === "storewide") return "Storewide offer";
  if (offer.targetType === "category") return "Category deal";
  if (offer.targetType === "product") return "Product deal";
  return "Pickup special";
}
