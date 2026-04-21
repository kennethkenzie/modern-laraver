"use client";

import { useEffect, useMemo, useRef, useState } from "react";
import {
  BadgePercent,
  CalendarRange,
  CheckCircle2,
  Gift,
  ImagePlus,
  Loader2,
  Percent,
  Plus,
  Save,
  Tag,
  Trash2,
  Truck,
  Upload,
  X,
} from "lucide-react";
import { useFrontendData } from "@/lib/use-frontend-data";
import { writeFrontendData } from "@/lib/frontend-data-store";
import type { Category, FrontendData, Offer, PickupLocation } from "@/lib/frontend-data";

const AUTO_SAVE_DELAY_MS = 1000;

type ProductOption = {
  id: string;
  name: string;
  slug: string;
  category: string;
  isPublished: boolean;
};

function createOfferId() {
  return `offer-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
}

function createEmptyOffer(): Offer {
  const today = new Date();
  const nextWeek = new Date(today);
  nextWeek.setDate(today.getDate() + 7);

  return {
    id: createOfferId(),
    title: "New Offer",
    code: "",
    headline: "Limited-time offer",
    description: "",
    discountType: "percentage",
    discountValue: 10,
    startDate: today.toISOString().slice(0, 10),
    endDate: nextWeek.toISOString().slice(0, 10),
    targetType: "storewide",
    targetValue: "",
    targetImage: "",
    targetTitle: "",
    badgeText: "Limited deal",
    bannerImage: "",
    isActive: true,
    isFeatured: false,
    stackable: false,
  };
}

export default function AdminOffersPage() {
  const { data, isLoading } = useFrontendData();
  const [offers, setOffers] = useState<Offer[]>([]);
  const [products, setProducts] = useState<ProductOption[]>([]);
  const [isProductsLoading, setIsProductsLoading] = useState(true);
  const [selectedId, setSelectedId] = useState<string>("");
  const [saveState, setSaveState] = useState<"idle" | "dirty" | "saved" | "error">("idle");
  const [isSaving, setIsSaving] = useState(false);
  const [isUploadingBanner, setIsUploadingBanner] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const hasHydratedRef = useRef(false);
  const pendingChangesRef = useRef(false);
  const autoSaveTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (isLoading || !data) return;

    if (!hasHydratedRef.current || !pendingChangesRef.current) {
      const nextOffers = JSON.parse(JSON.stringify(data.offers ?? [])) as Offer[];
      setOffers(nextOffers);
      setSelectedId((current) => current || nextOffers[0]?.id || "");
      hasHydratedRef.current = true;
    }
  }, [data, isLoading]);

  useEffect(() => {
    let active = true;

    const loadProducts = async () => {
      setIsProductsLoading(true);
      try {
        const response = await fetch("/api/admin/products", { cache: "no-store" });
        if (!response.ok) throw new Error("Failed to fetch products.");
        const payload = (await response.json()) as { products?: ProductOption[] };

        if (active) setProducts(payload.products ?? []);
      } catch (error) {
        console.error("Failed to load products for offers.", error);
        if (active) setProducts([]);
      } finally {
        if (active) setIsProductsLoading(false);
      }
    };

    void loadProducts();
    return () => {
      active = false;
    };
  }, []);

  useEffect(() => {
    return () => {
      if (autoSaveTimerRef.current) clearTimeout(autoSaveTimerRef.current);
    };
  }, []);

  useEffect(() => {
    if (!hasHydratedRef.current || !pendingChangesRef.current) return;

    if (autoSaveTimerRef.current) {
      clearTimeout(autoSaveTimerRef.current);
    }

    setSaveState("dirty");
    autoSaveTimerRef.current = setTimeout(() => {
      void persistOffers(offers);
    }, AUTO_SAVE_DELAY_MS);
  }, [offers]);

  const selectedOffer = useMemo(
    () => offers.find((offer) => offer.id === selectedId) ?? null,
    [offers, selectedId]
  );

  const categoryOptions = useMemo(
    () => (data?.categories ?? []).filter((category) => category.isActive),
    [data]
  );

  const pickupOptions = useMemo(
    () => (data?.pickupLocations ?? []).filter((location) => location.isActive),
    [data]
  );

  if (isLoading) {
    return (
      <div className="flex h-[600px] flex-col items-center justify-center rounded-[32px] bg-white border-2 border-dashed border-gray-100 shadow-sm transition-all animate-pulse">
        <Loader2 className="h-10 w-10 animate-spin text-[#114f8f] opacity-20" />
        <p className="mt-4 text-[11px] font-black uppercase tracking-widest text-gray-400">Loading Offers...</p>
      </div>
    );
  }

  const persistOffers = async (nextOffers: Offer[]) => {
    if (!data) return;

    setIsSaving(true);
    try {
      const payload: FrontendData = {
        ...data,
        offers: nextOffers,
      };

      await writeFrontendData(payload);
      pendingChangesRef.current = false;
      setSaveState("saved");
    } catch (error) {
      console.error("Failed to save offers.", error);
      setSaveState("error");
    } finally {
      setIsSaving(false);
    }
  };

  const updateOffers = (updater: (current: Offer[]) => Offer[]) => {
    setOffers((current) => {
      pendingChangesRef.current = true;
      return updater(current);
    });
  };

  const updateOffer = (id: string, patch: Partial<Offer>) => {
    updateOffers((current) =>
      current.map((offer) => (offer.id === id ? { ...offer, ...patch } : offer))
    );
  };

  const addOffer = () => {
    const next = createEmptyOffer();
    next.targetValue = pickRandomProducts();
    updateOffers((current) => [next, ...current]);
    setSelectedId(next.id);
  };

  const deleteOffer = (id: string) => {
    updateOffers((current) => current.filter((offer) => offer.id !== id));
    setSelectedId((current) => {
      if (current !== id) return current;
      const remaining = offers.filter((offer) => offer.id !== id);
      return remaining[0]?.id || "";
    });
  };

  const handleManualSave = async () => {
    if (autoSaveTimerRef.current) clearTimeout(autoSaveTimerRef.current);
    pendingChangesRef.current = true;
    await persistOffers(offers);
  };

  const pickRandomProducts = (count = 6): string => {
    const published = products.filter((p) => p.isPublished);
    if (published.length === 0) return "";
    const shuffled = [...published].sort(() => Math.random() - 0.5);
    return shuffled.slice(0, Math.min(count, shuffled.length)).map((p) => p.slug).join(",");
  };

  const handleTargetTypeChange = (offerId: string, nextType: Offer["targetType"]) => {
    updateOffer(offerId, {
      targetType: nextType,
      targetValue: nextType === "storewide" ? pickRandomProducts() : "",
      bannerImage: nextType === "banner" ? selectedOffer?.bannerImage || "" : "",
    });
  };

  const handleBannerUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file || !selectedOffer) return;

    setIsUploadingBanner(true);
    try {
      const formData = new FormData();
      formData.append("file", file);

      const response = await fetch("/api/upload", {
        method: "POST",
        body: formData,
      });

      const payload = (await response.json()) as { url?: string; error?: string };
      if (!response.ok || !payload.url) {
        throw new Error(payload.error || "Banner upload failed.");
      }

      updateOffer(selectedOffer.id, { bannerImage: payload.url });
    } catch (error) {
      console.error("Failed to upload offer banner.", error);
      alert("Failed to upload offer banner.");
    } finally {
      setIsUploadingBanner(false);
      if (fileInputRef.current) fileInputRef.current.value = "";
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div className="flex items-start gap-4">
          <span className="mt-2 h-2.5 w-10 rounded-full bg-[#f6c400]" />
          <div>
            <h1 className="text-[34px] font-black uppercase tracking-tight text-[#111827]">
              Create Offer
            </h1>
            <p className="mt-2 text-[12px] font-black uppercase tracking-[0.25em] text-gray-400">
              Build live promos, seasonal campaigns and coupon offers
            </p>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <StatusPill saveState={saveState} />
          <button
            type="button"
            onClick={handleManualSave}
            disabled={isSaving}
            className="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#114f8f] px-5 text-[13px] font-black uppercase tracking-widest text-white transition hover:bg-[#0d3f74] disabled:opacity-60"
          >
            {isSaving ? <Loader2 size={16} className="animate-spin" /> : <Save size={16} />}
            Save
          </button>
        </div>
      </div>

      <input
        ref={fileInputRef}
        type="file"
        accept="image/*,.svg"
        className="hidden"
        onChange={handleBannerUpload}
      />

      <div className="grid gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
        <section className="rounded-xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
          <div className="flex items-center justify-between gap-3 border-b border-[#eef1f4] pb-4">
            <div>
              <h2 className="text-[18px] font-semibold text-[#0b1220]">Offers</h2>
              <p className="mt-1 text-[13px] text-[#7b8394]">
                Manage current and scheduled campaigns
              </p>
            </div>
            <button
              type="button"
              onClick={addOffer}
              className="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#f6c400] px-4 text-[13px] font-black text-[#111827] transition hover:bg-[#ffcf00]"
            >
              <Plus size={16} />
              New
            </button>
          </div>

          <div className="mt-4 space-y-3">
            {offers.length === 0 ? (
              <button
                type="button"
                onClick={addOffer}
                className="flex w-full flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-[#d1d5db] bg-[#f9fafb] px-5 py-10 text-center transition hover:border-[#114f8f]/30 hover:bg-[#f8fbff]"
              >
                <Gift size={24} className="text-[#114f8f]" />
                <span className="text-[15px] font-semibold text-[#111827]">
                  Create your first offer
                </span>
                <span className="text-[13px] text-[#6b7280]">
                  Add coupon codes, timed discounts, or pickup promotions.
                </span>
              </button>
            ) : (
              offers.map((offer) => (
                <button
                  key={offer.id}
                  type="button"
                  onClick={() => setSelectedId(offer.id)}
                  className={[
                    "w-full rounded-xl border px-4 py-4 text-left transition",
                    selectedId === offer.id
                      ? "border-[#114f8f] bg-[#f8fbff] shadow-sm"
                      : "border-[#eef1f4] bg-white hover:border-[#cbd5e1]",
                  ].join(" ")}
                >
                  <div className="flex items-start justify-between gap-3">
                    <div className="min-w-0">
                      <div className="truncate text-[15px] font-semibold text-[#111827]">
                        {offer.title || "Untitled Offer"}
                      </div>
                      <div className="mt-1 flex items-center gap-2 text-[12px] text-[#6b7280]">
                        <span className="rounded-full bg-[#fff7ed] px-2 py-1 font-semibold text-[#c2410c]">
                          {formatDiscountLabel(offer)}
                        </span>
                        {offer.code ? (
                          <span className="rounded-full bg-[#f3f4f6] px-2 py-1 font-semibold text-[#374151]">
                            {offer.code}
                          </span>
                        ) : null}
                      </div>
                    </div>
                    <span
                      className={[
                        "rounded-full px-2.5 py-1 text-[11px] font-bold",
                        offer.isActive
                          ? "bg-[#ecfdf3] text-[#027a48]"
                          : "bg-[#f3f4f6] text-[#6b7280]",
                      ].join(" ")}
                    >
                      {offer.isActive ? "Active" : "Inactive"}
                    </span>
                  </div>
                  <p className="mt-3 line-clamp-2 text-[13px] text-[#6b7280]">
                    {offer.headline || "No headline yet"}
                  </p>
                </button>
              ))
            )}
          </div>
        </section>

        <section className="rounded-xl border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
          {selectedOffer ? (
            <div className="space-y-6">
              <div className="flex flex-col gap-4 border-b border-[#eef1f4] pb-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                  <h2 className="text-[24px] font-semibold text-[#0b1220]">
                    {selectedOffer.title || "Untitled Offer"}
                  </h2>
                  <p className="mt-2 text-[14px] text-[#6b7280]">
                    Configure the offer details, timing, targeting, and promotion label.
                  </p>
                </div>

                <button
                  type="button"
                  onClick={() => deleteOffer(selectedOffer.id)}
                  className="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#fecaca] px-4 text-[13px] font-semibold text-[#dc2626] transition hover:bg-[#fef2f2]"
                >
                  <Trash2 size={15} />
                  Delete
                </button>
              </div>

              <div className="grid gap-6 lg:grid-cols-2">
                <Field label="Offer title" icon={<Gift size={14} />}>
                  <input
                    value={selectedOffer.title}
                    onChange={(event) =>
                      updateOffer(selectedOffer.id, { title: event.target.value })
                    }
                    className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
                    placeholder="Weekend electronics sale"
                  />
                </Field>

                <Field label="Coupon code" icon={<Tag size={14} />}>
                  <input
                    value={selectedOffer.code}
                    onChange={(event) =>
                      updateOffer(selectedOffer.id, {
                        code: event.target.value.toUpperCase(),
                      })
                    }
                    className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] uppercase text-[#111827] outline-none transition focus:border-[#114f8f]"
                    placeholder="SAVE10"
                  />
                </Field>

                <Field label="Headline" icon={<BadgePercent size={14} />}>
                  <input
                    value={selectedOffer.headline}
                    onChange={(event) =>
                      updateOffer(selectedOffer.id, { headline: event.target.value })
                    }
                    className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
                    placeholder="Save big on selected products"
                  />
                </Field>

                <Field label="Badge text" icon={<Percent size={14} />}>
                  <input
                    value={selectedOffer.badgeText}
                    onChange={(event) =>
                      updateOffer(selectedOffer.id, { badgeText: event.target.value })
                    }
                    className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
                    placeholder="Flash deal"
                  />
                </Field>

                <Field label="Discount type" icon={<Percent size={14} />}>
                  <select
                    value={selectedOffer.discountType}
                    onChange={(event) =>
                      updateOffer(selectedOffer.id, {
                        discountType: event.target.value as Offer["discountType"],
                      })
                    }
                    className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
                  >
                    <option value="percentage">Percentage</option>
                    <option value="fixed">Fixed amount</option>
                    <option value="free_shipping">Free shipping</option>
                  </select>
                </Field>

                <Field label="Discount value" icon={<BadgePercent size={14} />}>
                  <input
                    type="number"
                    min="0"
                    value={selectedOffer.discountValue}
                    onChange={(event) =>
                      updateOffer(selectedOffer.id, {
                        discountValue: Number(event.target.value) || 0,
                      })
                    }
                    className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
                    placeholder="10"
                    disabled={selectedOffer.discountType === "free_shipping"}
                  />
                </Field>

                <Field label="Start date" icon={<CalendarRange size={14} />}>
                  <input
                    type="date"
                    value={selectedOffer.startDate}
                    onChange={(event) =>
                      updateOffer(selectedOffer.id, { startDate: event.target.value })
                    }
                    className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
                  />
                </Field>

                <Field label="End date" icon={<CalendarRange size={14} />}>
                  <input
                    type="date"
                    value={selectedOffer.endDate}
                    onChange={(event) =>
                      updateOffer(selectedOffer.id, { endDate: event.target.value })
                    }
                    className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
                  />
                </Field>

                <Field label="Target type" icon={<Truck size={14} />}>
                  <select
                    value={selectedOffer.targetType}
                    onChange={(event) =>
                      handleTargetTypeChange(
                        selectedOffer.id,
                        event.target.value as Offer["targetType"]
                      )
                    }
                    className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
                  >
                    <option value="storewide">Storewide</option>
                    <option value="category">Category</option>
                    <option value="product">Product</option>
                    <option value="pickup">Pickup only</option>
                    <option value="banner">Banner</option>
                  </select>
                </Field>

                <Field label="Target value" icon={<Tag size={14} />}>
                  <TargetValueField
                    offer={selectedOffer}
                    categories={categoryOptions}
                    products={products}
                    pickupLocations={pickupOptions}
                    isProductsLoading={isProductsLoading}
                    onChange={(value) => updateOffer(selectedOffer.id, { targetValue: value })}
                    onShuffle={() => updateOffer(selectedOffer.id, { targetValue: pickRandomProducts() })}
                  />
                </Field>
              </div>

              {selectedOffer.targetType === "banner" ? (
                <div className="rounded-xl border border-[#e5e7eb] bg-[#f9fafb] p-5">
                  <div className="flex items-center justify-between gap-3">
                    <div>
                      <div className="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.2em] text-[#6b7280]">
                        <ImagePlus size={14} />
                        Banner Upload
                      </div>
                      <p className="mt-2 text-[14px] text-[#6b7280]">
                        Upload the banner image to use for this offer card.
                      </p>
                    </div>

                    <button
                      type="button"
                      onClick={() => fileInputRef.current?.click()}
                      disabled={isUploadingBanner}
                      className="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#114f8f] px-4 text-[13px] font-black uppercase tracking-widest text-white transition hover:bg-[#0d3f74] disabled:opacity-60"
                    >
                      {isUploadingBanner ? (
                        <Loader2 size={16} className="animate-spin" />
                      ) : (
                        <Upload size={16} />
                      )}
                      Upload
                    </button>
                  </div>

                  <div className="mt-5">
                    {selectedOffer.bannerImage ? (
                      <div className="relative overflow-hidden rounded-xl border border-[#dbe2ea] bg-white">
                        <img
                          src={selectedOffer.bannerImage}
                          alt={selectedOffer.title}
                          className="h-[220px] w-full object-cover"
                        />
                        <button
                          type="button"
                          onClick={() => updateOffer(selectedOffer.id, { bannerImage: "" })}
                          className="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-black/75"
                        >
                          <X size={15} />
                        </button>
                      </div>
                    ) : (
                      <button
                        type="button"
                        onClick={() => fileInputRef.current?.click()}
                        className="flex min-h-[220px] w-full flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-[#cbd5e1] bg-white text-center transition hover:border-[#114f8f]/35 hover:bg-[#f8fbff]"
                      >
                        <ImagePlus size={24} className="text-[#114f8f]" />
                        <span className="text-[15px] font-semibold text-[#111827]">
                          Upload banner image
                        </span>
                        <span className="text-[13px] text-[#6b7280]">
                          PNG, JPG, WEBP or SVG for promo cards and hero highlights.
                        </span>
                      </button>
                    )}
                  </div>
                </div>
              ) : null}

              <Field label="Description" icon={<Gift size={14} />}>
                <textarea
                  value={selectedOffer.description}
                  onChange={(event) =>
                    updateOffer(selectedOffer.id, { description: event.target.value })
                  }
                  className="min-h-[120px] w-full rounded-xl border border-[#dbe2ea] bg-white px-4 py-3 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
                  placeholder="Explain the promo conditions, campaign notes, or where this offer should appear."
                />
              </Field>
              <div className="grid gap-4 lg:grid-cols-3">
                <ToggleCard
                  label="Offer active"
                  description="Controls whether customers should be eligible for this promo."
                  checked={selectedOffer.isActive}
                  onChange={(value) => updateOffer(selectedOffer.id, { isActive: value })}
                />
                <ToggleCard
                  label="Feature this offer"
                  description="Useful for banners, homepage promos, or highlighted campaigns."
                  checked={selectedOffer.isFeatured}
                  onChange={(value) => updateOffer(selectedOffer.id, { isFeatured: value })}
                />
                <ToggleCard
                  label="Allow stacking"
                  description="Lets this offer combine with other discounts or coupon codes."
                  checked={selectedOffer.stackable}
                  onChange={(value) => updateOffer(selectedOffer.id, { stackable: value })}
                />
              </div>

              <div className="rounded-xl border border-[#e5e7eb] bg-[#f9fafb] p-5">
                <div className="flex items-center gap-2 text-[13px] font-black uppercase tracking-[0.2em] text-[#6b7280]">
                  <CheckCircle2 size={14} className="text-[#114f8f]" />
                  Preview
                </div>
                <div className="mt-4 overflow-hidden rounded-xl border border-[#eef1f4] bg-white">
                  {selectedOffer.bannerImage ? (
                    <img
                      src={selectedOffer.bannerImage}
                      alt={selectedOffer.title}
                      className="h-[180px] w-full object-cover"
                    />
                  ) : null}
                  <div className="p-5">
                    <div className="flex flex-wrap items-center gap-3">
                      <span className="rounded-full bg-[#cc0c39] px-3 py-1 text-[12px] font-bold text-white">
                        {selectedOffer.badgeText || "Promo"}
                      </span>
                      <span className="text-[20px] font-semibold text-[#111827]">
                        {selectedOffer.headline || "Limited-time offer"}
                      </span>
                    </div>
                    <p className="mt-3 text-[14px] text-[#4b5563]">
                      {selectedOffer.description || "No description added yet."}
                    </p>
                    <div className="mt-4 flex flex-wrap items-center gap-3 text-[13px] text-[#6b7280]">
                      <span className="rounded-full bg-[#fff7ed] px-3 py-1 font-semibold text-[#c2410c]">
                        {formatDiscountLabel(selectedOffer)}
                      </span>
                      <span>
                        {selectedOffer.startDate} to {selectedOffer.endDate}
                      </span>
                      <span>{getTargetSummary(selectedOffer, categoryOptions, products, pickupOptions)}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          ) : (
            <div className="flex min-h-[420px] flex-col items-center justify-center rounded-xl border border-dashed border-[#d1d5db] bg-[#fafafa] px-8 text-center">
              <Gift size={28} className="text-[#114f8f]" />
              <h2 className="mt-4 text-[24px] font-semibold text-[#111827]">
                No offer selected
              </h2>
              <p className="mt-2 max-w-[420px] text-[14px] text-[#6b7280]">
                Create a new offer to manage coupon codes, scheduled discounts, or pickup
                promotions from one place.
              </p>
              <button
                type="button"
                onClick={addOffer}
                className="mt-6 inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#f6c400] px-5 text-[13px] font-black text-[#111827] transition hover:bg-[#ffcf00]"
              >
                <Plus size={16} />
                Create Offer
              </button>
            </div>
          )}
        </section>
      </div>
    </div>
  );
}

function TargetValueField({
  offer,
  categories,
  products,
  pickupLocations,
  isProductsLoading,
  onChange,
  onShuffle,
}: {
  offer: Offer;
  categories: Category[];
  products: ProductOption[];
  pickupLocations: PickupLocation[];
  isProductsLoading: boolean;
  onChange: (value: string) => void;
  onShuffle: () => void;
}) {
  if (offer.targetType === "storewide") {
    const selectedSlugs = offer.targetValue ? offer.targetValue.split(",").filter(Boolean) : [];
    const selectedProducts = selectedSlugs
      .map((slug) => products.find((p) => p.slug === slug))
      .filter(Boolean) as ProductOption[];

    return (
      <div className="space-y-3">
        <div className="flex items-center gap-2">
          <input
            value="Whole store"
            readOnly
            className="h-12 w-full rounded-xl border border-[#e5e7eb] bg-[#f9fafb] px-4 text-[14px] text-[#6b7280] outline-none"
          />
          <button
            type="button"
            onClick={onShuffle}
            className="inline-flex h-12 shrink-0 items-center gap-2 rounded-xl border border-[#dbe2ea] bg-white px-4 text-[13px] font-semibold text-[#114f8f] transition hover:bg-[#f8fbff]"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="16 3 21 3 21 8" /><line x1="4" y1="20" x2="21" y2="3" /><polyline points="21 16 21 21 16 21" /><line x1="15" y1="15" x2="21" y2="21" /><line x1="4" y1="4" x2="9" y2="9" /></svg>
            Shuffle
          </button>
        </div>
        {selectedProducts.length > 0 && (
          <div className="rounded-xl border border-[#e5e7eb] bg-[#f9fafb] p-3">
            <p className="mb-2 text-[11px] font-black uppercase tracking-[0.15em] text-[#6b7280]">
              Random discount products ({selectedProducts.length})
            </p>
            <div className="flex flex-wrap gap-2">
              {selectedProducts.map((p) => (
                <span
                  key={p.slug}
                  className="inline-flex items-center gap-1.5 rounded-lg bg-white border border-[#dbe2ea] px-3 py-1.5 text-[12px] font-medium text-[#111827]"
                >
                  <BadgePercent size={12} className="text-[#f6c400]" />
                  {p.name}
                </span>
              ))}
            </div>
          </div>
        )}
        {selectedProducts.length === 0 && products.length > 0 && (
          <p className="text-[12px] text-[#9ca3af]">
            Click Shuffle to pick random products for this storewide offer.
          </p>
        )}
      </div>
    );
  }

  if (offer.targetType === "category") {
    return (
      <select
        value={offer.targetValue}
        onChange={(event) => onChange(event.target.value)}
        className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
      >
        <option value="">Select category</option>
        {categories.map((category) => (
          <option key={category.id} value={category.slug}>
            {category.title}
          </option>
        ))}
      </select>
    );
  }

  if (offer.targetType === "product") {
    return (
      <select
        value={offer.targetValue}
        onChange={(event) => onChange(event.target.value)}
        disabled={isProductsLoading}
        className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f] disabled:bg-[#f9fafb] disabled:text-[#6b7280]"
      >
        <option value="">
          {isProductsLoading ? "Loading products..." : "Select product"}
        </option>
        {products
          .filter((product) => product.isPublished)
          .map((product) => (
            <option key={product.id} value={product.slug}>
              {product.name}
            </option>
          ))}
      </select>
    );
  }

  if (offer.targetType === "pickup") {
    return (
      <select
        value={offer.targetValue}
        onChange={(event) => onChange(event.target.value)}
        className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
      >
        <option value="">Select pickup location</option>
        {pickupLocations.map((location) => (
          <option key={location.id} value={location.id}>
            {location.title}
          </option>
        ))}
      </select>
    );
  }

  return (
    <input
      value={offer.targetValue}
      onChange={(event) => onChange(event.target.value)}
      className="h-12 w-full rounded-xl border border-[#dbe2ea] bg-white px-4 text-[14px] text-[#111827] outline-none transition focus:border-[#114f8f]"
      placeholder="Optional banner click destination"
    />
  );
}

function Field({
  label,
  icon,
  children,
}: {
  label: string;
  icon: React.ReactNode;
  children: React.ReactNode;
}) {
  return (
    <label className="block space-y-2">
      <span className="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.2em] text-[#6b7280]">
        {icon}
        {label}
      </span>
      {children}
    </label>
  );
}

function ToggleCard({
  label,
  description,
  checked,
  onChange,
}: {
  label: string;
  description: string;
  checked: boolean;
  onChange: (value: boolean) => void;
}) {
  return (
    <button
      type="button"
      onClick={() => onChange(!checked)}
      className={[
        "rounded-xl border p-4 text-left transition",
        checked ? "border-[#114f8f] bg-[#f8fbff]" : "border-[#e5e7eb] bg-white",
      ].join(" ")}
    >
      <div className="flex items-center justify-between gap-3">
        <span className="text-[15px] font-semibold text-[#111827]">{label}</span>
        <span
          className={[
            "rounded-full px-2.5 py-1 text-[11px] font-bold",
            checked ? "bg-[#ecfdf3] text-[#027a48]" : "bg-[#f3f4f6] text-[#6b7280]",
          ].join(" ")}
        >
          {checked ? "On" : "Off"}
        </span>
      </div>
      <p className="mt-2 text-[13px] leading-5 text-[#6b7280]">{description}</p>
    </button>
  );
}

function StatusPill({
  saveState,
}: {
  saveState: "idle" | "dirty" | "saved" | "error";
}) {
  if (saveState === "idle") return null;

  const config = {
    dirty: {
      label: "Autosave pending",
      className: "border-amber-100 bg-amber-50 text-amber-700",
    },
    saved: {
      label: "Saved",
      className: "border-emerald-100 bg-emerald-50 text-emerald-600",
    },
    error: {
      label: "Save failed",
      className: "border-red-100 bg-red-50 text-red-600",
    },
  }[saveState];

  return (
    <div
      className={`inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-[12px] font-black uppercase tracking-wider ${config.className}`}
    >
      <CheckCircle2 size={15} />
      {config.label}
    </div>
  );
}

function formatDiscountLabel(offer: Offer) {
  if (offer.discountType === "free_shipping") return "Free shipping";
  if (offer.discountType === "fixed") return `UGX ${offer.discountValue} off`;
  return `${offer.discountValue}% off`;
}

function getTargetSummary(
  offer: Offer,
  categories: Category[],
  products: ProductOption[],
  pickupLocations: PickupLocation[]
) {
  if (offer.targetType === "storewide") return "Target: storewide";
  if (offer.targetType === "banner") return "Target: banner campaign";

  if (offer.targetType === "category") {
    const match = categories.find((category) => category.slug === offer.targetValue);
    return `Target: category / ${match?.title || "Not selected"}`;
  }

  if (offer.targetType === "product") {
    const match = products.find((product) => product.slug === offer.targetValue);
    return `Target: product / ${match?.name || "Not selected"}`;
  }

  const match = pickupLocations.find((location) => location.id === offer.targetValue);
  return `Target: pickup / ${match?.title || "Not selected"}`;
}
