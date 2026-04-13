"use client";

import Link from "next/link";
import { Suspense, useEffect, useMemo, useRef, useState } from "react";
import { ChevronDown, Loader2, Plus, RefreshCw, Trash2, Upload, X } from "lucide-react";
import { useFrontendData } from "@/lib/use-frontend-data";
import { useSearchParams } from "next/navigation";
import QuillEditor from "@/components/QuillEditor";
import { normalizeMediaUrl } from "@/lib/media";

const tabs = ["Product Information", "Images & Videos", "Product Price & Stock", "Description & Specification", "Shipping Info", "Others", "SEO"];

type MediaItem = { id: string; name: string; url: string; kind: "image" | "video"; uploading?: boolean };
type VariantItem = { id: string; label: string; sku: string; price: string; compareAtPrice: string; stockQty: string };
type SpecItem = { id: string; label: string; value: string };
type SelectOption = string | { value: string; label: string; level?: number };

type FormState = {
  productName: string;
  category: string;
  brand: string;
  unit: string;
  minimumOrderQuantity: string;
  barcode: string;
  tags: string;
  slug: string;
  isDigitalProduct: boolean;
  currencyCode: string;
  taxClass: string;
  lowStockThreshold: string;
  shortDescription: string;
  description: string;
  shippingWeight: string;
  shippingLength: string;
  shippingWidth: string;
  shippingHeight: string;
  shippingClass: string;
  dispatchTime: string;
  shippingNotes: string;
  productType: string;
  condition: string;
  visibility: string;
  warranty: string;
  featured: boolean;
  returnable: boolean;
  seoTitle: string;
  seoDescription: string;
  seoKeywords: string;
};

const initialForm: FormState = {
  productName: "",
  category: "Select Category",
  brand: "Select Brand",
  unit: "",
  minimumOrderQuantity: "1",
  barcode: "",
  tags: "",
  slug: "",
  isDigitalProduct: false,
  currencyCode: "UGX",
  taxClass: "Standard",
  lowStockThreshold: "5",
  shortDescription: "",
  description: "",
  shippingWeight: "",
  shippingLength: "",
  shippingWidth: "",
  shippingHeight: "",
  shippingClass: "Standard Parcel",
  dispatchTime: "1-2 business days",
  shippingNotes: "",
  productType: "Physical",
  condition: "New",
  visibility: "Published",
  warranty: "7 days",
  featured: false,
  returnable: true,
  seoTitle: "",
  seoDescription: "",
  seoKeywords: "",
};

export default function ProductInformationPage() {
  return (
    <Suspense fallback={<div className="p-6 text-sm text-gray-500">Loading product editor...</div>}>
      <ProductInformationPageInner />
    </Suspense>
  );
}

function ProductInformationPageInner() {
  const { data } = useFrontendData();
  const searchParams = useSearchParams();
  const editId = searchParams.get("edit");
  const uploadRef = useRef<HTMLInputElement>(null);
  const [activeTab, setActiveTab] = useState(0);
  const [form, setForm] = useState<FormState>(initialForm);
  const [media, setMedia] = useState<MediaItem[]>([]);
  const [variants, setVariants] = useState<VariantItem[]>([{ id: crypto.randomUUID(), label: "Default", sku: "", price: "", compareAtPrice: "", stockQty: "" }]);
  const [specs, setSpecs] = useState<SpecItem[]>([]);
  const [bullets, setBullets] = useState(["", "", ""]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isLoadingProduct, setIsLoadingProduct] = useState(false);
  const [message, setMessage] = useState("");
  const [messageTone, setMessageTone] = useState<"success" | "error">("success");

  const categoryOptions = useMemo(() => {
    const activeCategories = (data?.categories ?? []).filter((c) => c.isActive);
    const byParent = new Map<string, typeof activeCategories>();

    for (const category of activeCategories) {
      const parentKey = category.rootCategory?.trim() || "__root__";
      const siblings = byParent.get(parentKey) ?? [];
      siblings.push(category);
      byParent.set(parentKey, siblings);
    }

    for (const siblings of byParent.values()) {
      siblings.sort((a, b) => a.order - b.order || a.title.localeCompare(b.title));
    }

    const orderedOptions: Array<{ value: string; label: string; level: number }> = [];
    const seen = new Set<string>();

    const visit = (parentTitle: string | null, depth: number) => {
      const key = parentTitle?.trim() || "__root__";
      const children = byParent.get(key) ?? [];

      for (const category of children) {
        if (seen.has(category.title)) continue;
        seen.add(category.title);
        const prefix = depth > 0 ? `${"-".repeat(depth)} ` : "";
        orderedOptions.push({
          value: category.title,
          label: `${prefix}${category.title}`,
          level: depth,
        });
        visit(category.title, depth + 1);
      }
    };

    visit(null, 0);

    const currentCategory =
      form.category && form.category !== "Select Category" && !seen.has(form.category)
        ? [{ value: form.category, label: form.category, level: 0 }]
        : [];

    return [
      { value: "Select Category", label: "Select Category", level: 0 },
      ...orderedOptions,
      ...currentCategory,
    ];
  }, [data, form.category]);

  const brandOptions = useMemo(() => {
    const activeBrands = (data?.brands ?? [])
      .filter((b) => b.isActive)
      .map((b) => b.title);

    return [
      "Select Brand",
      ...Array.from(new Set(activeBrands)),
    ];
  }, [data]);

  useEffect(() => {
    const hasCategory = categoryOptions.some((option) =>
      (typeof option === "string" ? option : option.value) === form.category
    );
    if (!hasCategory) setForm((current) => ({ ...current, category: "Select Category" }));
  }, [categoryOptions, form.category]);

  useEffect(() => {
    if (!brandOptions.includes(form.brand)) setForm((current) => ({ ...current, brand: "Select Brand" }));
  }, [brandOptions, form.brand]);

  useEffect(() => {
    if (!editId) return;

    let cancelled = false;

    const loadProduct = async () => {
      setIsLoadingProduct(true);
      try {
        const response = await fetch(`/api/admin/products/${editId}`, { cache: "no-store" });
        const payload = (await response.json()) as {
          error?: string;
          product?: {
            name: string;
            slug: string;
            category: string;
            brand: string;
            currencyCode: string;
            shortDescription: string;
            description: string;
            featured: boolean;
            shippingLabel: string;
            deliveryLabel: string;
            returnsLabel: string;
            paymentLabel: string;
            media: MediaItem[];
            variants: VariantItem[];
            specs: SpecItem[];
            bullets: string[];
            isPublished: boolean;
          };
        };

        if (!response.ok || !payload.product) {
          throw new Error(payload.error || "Failed to load product.");
        }

        if (cancelled) return;

        const product = payload.product;
        const knownSpecs = new Map(product.specs.map((spec) => [spec.label, spec.value]));
        const customSpecs = product.specs.filter((spec) => !SYSTEM_SPEC_LABELS.has(spec.label));

        setForm((current) => ({
          ...current,
          productName: product.name,
          slug: product.slug,
          category: product.category || "Select Category",
          brand: product.brand || "Select Brand",
          currencyCode: product.currencyCode || "UGX",
          shortDescription: product.shortDescription || "",
          description: product.description || "",
          featured: product.featured,
          visibility: product.isPublished ? "Published" : "Draft",
          unit: knownSpecs.get("Unit") || "",
          minimumOrderQuantity: knownSpecs.get("Minimum Order Quantity") || "1",
          barcode: knownSpecs.get("Barcode") || "",
          tags: knownSpecs.get("Tags") || "",
          taxClass: knownSpecs.get("Tax Class") || "Standard",
          lowStockThreshold: knownSpecs.get("Low Stock Alert") || "5",
          productType: knownSpecs.get("Product Type") || "Physical",
          condition: knownSpecs.get("Condition") || "New",
          warranty: knownSpecs.get("Warranty") || "7 days",
          shippingNotes: knownSpecs.get("Shipping Notes") || "",
          seoTitle: knownSpecs.get("SEO Title") || "",
          seoDescription: knownSpecs.get("SEO Description") || "",
          seoKeywords: knownSpecs.get("SEO Keywords") || "",
          returnable: !((product.returnsLabel || "").toLowerCase().includes("not returnable")),
          shippingClass: (product.shippingLabel || "").split("|")[0]?.trim() || "Standard Parcel",
          dispatchTime: product.deliveryLabel || "1-2 business days",
        }));
        setMedia((product.media ?? []).map((item) => ({ ...item, url: normalizeMediaUrl(item.url) })));
        setVariants(product.variants?.length ? product.variants : [{ id: crypto.randomUUID(), label: "Default", sku: "", price: "", compareAtPrice: "", stockQty: "" }]);
        setSpecs(customSpecs);
        setBullets(product.bullets?.length ? product.bullets : ["", "", ""]);
      } catch (error) {
        console.error(error);
        if (!cancelled) {
          setMessageTone("error");
          setMessage(error instanceof Error ? error.message : "Failed to load product.");
        }
      } finally {
        if (!cancelled) setIsLoadingProduct(false);
      }
    };

    void loadProduct();

    return () => {
      cancelled = true;
    };
  }, [editId]);

  const updateForm = <K extends keyof FormState>(key: K, value: FormState[K]) => {
    setForm((current) => {
      const next = { ...current, [key]: value };
      if (key === "productName" && !current.slug.trim()) next.slug = slugify(String(value));
      return next;
    });
  };

  const uploadMediaFile = async (file: File, tempId: string) => {
    try {
      const body = new FormData();
      body.append("file", file);
      const response = await fetch("/api/upload", { method: "POST", body });
      const raw = await response.text();
      let payload: { url?: string; error?: string } = {};
      if (raw) {
        try { payload = JSON.parse(raw) as { url?: string; error?: string }; } catch { payload = { error: raw }; }
      }
      if (!response.ok || !payload.url) throw new Error(payload.error || "Upload failed.");
      setMedia((current) => current.map((item) => item.id === tempId ? { ...item, url: payload.url!, uploading: false } : item));
    } catch (error) {
      console.error(error);
      setMedia((current) => current.filter((item) => item.id !== tempId));
      alert(error instanceof Error ? error.message : "Upload failed.");
    }
  };

  const handleMediaSelect = (files: FileList | null) => {
    if (!files?.length) return;
    Array.from(files).forEach((file) => {
      const id = crypto.randomUUID();
      setMedia((current) => [...current, { id, name: file.name, url: URL.createObjectURL(file), kind: file.type.startsWith("video/") ? "video" : "image", uploading: true }]);
      void uploadMediaFile(file, id);
    });
  };

  const handleSubmit = async (action: "publish" | "draft") => {
    if (!form.productName.trim()) return alert("Product name is required.");
    if (form.category === "Select Category") return alert("Category is required.");
    if (form.brand === "Select Brand") return alert("Brand is required.");
    if (variants.some((variant) => !variant.price.trim())) {
      setActiveTab(2);
      return alert("Every variant needs a price.");
    }
    if (media.some((item) => item.uploading)) {
      setActiveTab(1);
      return alert("Wait for uploads to finish.");
    }

    setIsSubmitting(true);
    setMessage("");
    setMessageTone("success");
    try {
      const endpoint = editId ? `/api/admin/products/${editId}` : "/api/admin/products";
      const method = editId ? "PATCH" : "POST";
      const response = await fetch(endpoint, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: editId ? "update" : action,
          name: form.productName,
          slug: form.slug || slugify(form.productName),
          categoryName: form.category,
          brand: form.brand === "Select Brand" ? "" : form.brand,
          currencyCode: form.currencyCode,
          shortDescription: form.shortDescription,
          description: form.description,
          media: media.map((item) => ({ url: item.url, kind: item.kind, altText: form.productName })),
          variants,
          specs: buildSpecs(form, specs),
          bullets: bullets.filter((item) => item.trim()),
          featured: form.featured,
          shippingLabel: `${form.shippingClass}${form.shippingWeight ? ` | ${form.shippingWeight}kg` : ""}`,
          deliveryLabel: form.dispatchTime,
          returnsLabel: form.returnable ? `Returns allowed | ${form.warranty}` : "Not returnable",
          paymentLabel: `Tax: ${form.taxClass}`,
          bestsellerLabel: form.featured ? "Featured Product" : "",
          bestsellerCategory: form.category,
          boughtPastMonthLabel: form.tags ? `Tags: ${form.tags}` : "",
          publishState: action,
        }),
      });

      const raw = await response.text();
      let payload: { error?: string } = {};
      if (raw) {
        try { payload = JSON.parse(raw) as { error?: string }; } catch { payload = { error: raw }; }
      }
      if (!response.ok) throw new Error(payload.error || "Failed to save product.");

      setMessageTone("success");
      setMessage(
        editId
          ? action === "publish"
            ? "Product updated and published successfully."
            : "Product updated as draft."
          : action === "publish"
            ? "Product published successfully."
            : "Product saved as draft."
      );
      if (!editId) {
        setForm(initialForm);
        setMedia([]);
        setVariants([{ id: crypto.randomUUID(), label: "Default", sku: "", price: "", compareAtPrice: "", stockQty: "" }]);
        setSpecs([]);
        setBullets(["", "", ""]);
        setActiveTab(0);
      }
    } catch (error) {
      console.error(error);
      setMessageTone("error");
      setMessage(error instanceof Error ? error.message : "Failed to save product.");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="bg-[#f7f7f8] p-4 sm:p-6">
      <div className="mx-auto max-w-[1220px]">
        <div className="mb-6 flex items-start gap-3">
          <span className="mt-2 h-2.5 w-8 rounded-full bg-[#0b63ce]" />
          <div>
            <h1 className="text-[28px] font-semibold tracking-tight text-gray-900">{editId ? "Edit Product" : "Add New Product"}</h1>
            <p className="mt-1 text-sm text-gray-500">{editId ? "Update the saved product and publish, unpublish, or refine its details." : "Fill in the information below to register a new product."}</p>
          </div>
        </div>

        {isLoadingProduct ? (
          <div className="mb-4 flex items-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-3 text-sm text-gray-600">
            <Loader2 className="h-4 w-4 animate-spin" />
            Loading product data...
          </div>
        ) : null}

        <ProductTabs activeTab={activeTab} setActiveTab={setActiveTab} />

        <section className="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
          <div className="border-b border-gray-100 bg-gray-50/30 px-6 py-5">
            <h2 className="text-lg font-bold text-gray-900">{tabs[activeTab]}</h2>
          </div>

          <div className="px-6 py-8">
            {activeTab === 0 && (
              <div className="space-y-6">
                <Block><Label text="Product Name" required /><TextInput value={form.productName} onChange={(value) => updateForm("productName", value)} placeholder="e.g. Samsung Galaxy S24 Ultra" /></Block>
                <Grid2>
                  <div><Label text="Category" required /><HierarchicalSelect value={form.category} onChange={(value) => updateForm("category", value)} options={categoryOptions} muted={form.category === "Select Category"} /></div>
                  <div><Label text="Brand" required /><SelectInput value={form.brand} onChange={(value) => updateForm("brand", value)} options={brandOptions} muted={form.brand === "Select Brand"} /></div>
                </Grid2>
                <Grid2>
                  <div><Label text="Unit" required /><TextInput value={form.unit} onChange={(value) => updateForm("unit", value)} placeholder="e.g kg, pc, box" /></div>
                  <div><Label text="Min. Order Quantity" required /><TextInput value={form.minimumOrderQuantity} onChange={(value) => updateForm("minimumOrderQuantity", value)} type="number" /></div>
                </Grid2>
                <Block><Label text="Barcode" /><TextInput value={form.barcode} onChange={(value) => updateForm("barcode", value)} placeholder="Enter product barcode" /></Block>
                <Block><Label text="Tags" /><TextInput value={form.tags} onChange={(value) => updateForm("tags", value)} placeholder="smart tv, samsung, 4k" /></Block>
                <Block><Label text="Slug" /><TextInput value={form.slug} onChange={(value) => updateForm("slug", slugify(value))} placeholder="product-slug-here" /></Block>
                <ToggleRow label="Digital Product" description="If enabled, this product won't require shipping." enabled={form.isDigitalProduct} onToggle={() => updateForm("isDigitalProduct", !form.isDigitalProduct)} />
              </div>
            )}

            {activeTab === 1 && (
              <div className="space-y-8">
                <input ref={uploadRef} type="file" accept="image/*,video/*" multiple className="hidden" onChange={(event) => handleMediaSelect(event.target.files)} />
                <div className="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center">
                  <div className="mx-auto max-w-md">
                    <div className="text-lg font-semibold text-gray-900">Upload product media</div>
                    <p className="mt-2 text-sm text-gray-500">Files upload immediately and will be saved with the product.</p>
                    <button type="button" onClick={() => uploadRef.current?.click()} className="mt-5 inline-flex items-center gap-2 rounded-md bg-[#1f2937] px-5 py-3 text-sm font-bold text-white transition hover:bg-black"><Upload className="h-4 w-4" />Choose files</button>
                  </div>
                </div>
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                  {media.map((item) => (
                    <div key={item.id} className="overflow-hidden rounded-xl border border-gray-200 bg-white">
                      <div className="flex h-48 items-center justify-center bg-gray-100 p-3">
                        {item.kind === "image" ? <img src={item.url} alt={item.name} className="max-h-full w-full object-contain" /> : <video src={item.url} className="max-h-full w-full rounded-lg object-cover" controls />}
                      </div>
                      <div className="flex items-center justify-between gap-3 border-t border-gray-100 px-4 py-3">
                        <div className="min-w-0"><div className="truncate text-sm font-semibold text-gray-900">{item.name}</div><div className="text-[12px] uppercase tracking-wider text-gray-400">{item.uploading ? "Uploading..." : item.kind}</div></div>
                        <button type="button" onClick={() => setMedia((current) => current.filter((entry) => entry.id !== item.id))} className="inline-flex h-9 w-9 items-center justify-center rounded-full bg-red-50 text-red-500 transition hover:bg-red-100">{item.uploading ? <Loader2 className="h-4 w-4 animate-spin" /> : <X className="h-4 w-4" />}</button>
                      </div>
                    </div>
                  ))}
                </div>
                {media.length === 0 ? <EmptyState text="No images or videos added yet." /> : null}
              </div>
            )}

            {activeTab === 2 && (
              <div className="space-y-8">
                <Grid3>
                  <div><Label text="Currency" required /><SelectInput value={form.currencyCode} onChange={(value) => updateForm("currencyCode", value)} options={["UGX", "USD", "KES"]} /></div>
                  <div><Label text="Low Stock Alert" /><TextInput value={form.lowStockThreshold} onChange={(value) => updateForm("lowStockThreshold", value)} type="number" /></div>
                  <div><Label text="Tax Class" /><SelectInput value={form.taxClass} onChange={(value) => updateForm("taxClass", value)} options={["Standard", "Zero Rated", "Exempt"]} /></div>
                </Grid3>
                <div className="space-y-4">
                  {variants.map((variant, index) => (
                    <div key={variant.id} className="rounded-xl border border-gray-200 bg-gray-50 p-5">
                      <div className="mb-4 flex items-center justify-between gap-3">
                        <div className="text-sm font-semibold text-gray-900">Variant {index + 1}</div>
                        {variants.length > 1 ? <button type="button" onClick={() => setVariants((current) => current.filter((item) => item.id !== variant.id))} className="inline-flex h-9 w-9 items-center justify-center rounded-full bg-red-50 text-red-500 transition hover:bg-red-100"><Trash2 className="h-4 w-4" /></button> : null}
                      </div>
                      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                        <TextField label="Variant Name" value={variant.label} onChange={(value) => updateVariant(setVariants, variant.id, { label: value })} placeholder="Default" />
                        <TextField label="SKU" value={variant.sku} onChange={(value) => updateVariant(setVariants, variant.id, { sku: value })} placeholder="SKU-001" />
                        <TextField label="Price" value={variant.price} onChange={(value) => updateVariant(setVariants, variant.id, { price: value })} placeholder="120000" type="number" />
                        <TextField label="Compare At" value={variant.compareAtPrice} onChange={(value) => updateVariant(setVariants, variant.id, { compareAtPrice: value })} placeholder="150000" type="number" />
                        <TextField label="Stock Qty" value={variant.stockQty} onChange={(value) => updateVariant(setVariants, variant.id, { stockQty: value })} placeholder="20" type="number" />
                      </div>
                    </div>
                  ))}
                </div>
                <button type="button" onClick={() => setVariants((current) => [...current, { id: crypto.randomUUID(), label: `Variant ${current.length + 1}`, sku: "", price: "", compareAtPrice: "", stockQty: "" }])} className="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50"><Plus className="h-4 w-4" />Add variant</button>
              </div>
            )}

            {activeTab === 3 && (
              <div className="space-y-8">
                <Block><Label text="Short Description" /><TextArea value={form.shortDescription} onChange={(value) => updateForm("shortDescription", value)} placeholder="A short summary shown on cards and listing pages." /></Block>
                <Block>
                  <Label text="Full Description" />
                  <QuillEditor
                    value={form.description}
                    onChange={(value) => updateForm("description", value)}
                    placeholder="Describe the product, features, compatibility, and what is included in the box."
                  />
                </Block>
                <div>
                  <div className="mb-3 text-sm font-bold text-gray-700">About This Item</div>
                  <div className="space-y-3">
                    {bullets.map((item, index) => (
                      <div key={index} className="flex gap-3">
                        <input value={item} onChange={(event) => setBullets((current) => current.map((entry, currentIndex) => currentIndex === index ? event.target.value : entry))} placeholder={`Bullet point ${index + 1}`} className="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        <button type="button" onClick={() => setBullets((current) => current.filter((_, currentIndex) => currentIndex !== index))} className="inline-flex h-11 w-11 items-center justify-center rounded-md bg-red-50 text-red-500 transition hover:bg-red-100"><Trash2 className="h-4 w-4" /></button>
                      </div>
                    ))}
                  </div>
                  <button type="button" onClick={() => setBullets((current) => [...current, ""])} className="mt-3 inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50"><Plus className="h-4 w-4" />Add bullet</button>
                </div>
                <div>
                  <div className="mb-3 text-sm font-bold text-gray-700">Specifications</div>
                  <div className="space-y-3">
                    {specs.map((spec) => (
                      <div key={spec.id} className="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                        <input value={spec.label} onChange={(event) => updateSpec(setSpecs, spec.id, { label: event.target.value })} placeholder="Spec label" className="h-11 rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        <input value={spec.value} onChange={(event) => updateSpec(setSpecs, spec.id, { value: event.target.value })} placeholder="Spec value" className="h-11 rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce]" />
                        <button type="button" onClick={() => setSpecs((current) => current.filter((item) => item.id !== spec.id))} className="inline-flex h-11 w-11 items-center justify-center rounded-md bg-red-50 text-red-500 transition hover:bg-red-100"><Trash2 className="h-4 w-4" /></button>
                      </div>
                    ))}
                  </div>
                  <button type="button" onClick={() => setSpecs((current) => [...current, { id: crypto.randomUUID(), label: "", value: "" }])} className="mt-3 inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50"><Plus className="h-4 w-4" />Add specification</button>
                </div>
              </div>
            )}

            {activeTab === 4 && (
              <div className="space-y-8">
                <Grid4>
                  <TextField label="Weight (kg)" value={form.shippingWeight} onChange={(value) => updateForm("shippingWeight", value)} placeholder="0.5" type="number" />
                  <TextField label="Length (cm)" value={form.shippingLength} onChange={(value) => updateForm("shippingLength", value)} placeholder="20" type="number" />
                  <TextField label="Width (cm)" value={form.shippingWidth} onChange={(value) => updateForm("shippingWidth", value)} placeholder="10" type="number" />
                  <TextField label="Height (cm)" value={form.shippingHeight} onChange={(value) => updateForm("shippingHeight", value)} placeholder="8" type="number" />
                </Grid4>
                <Grid2>
                  <div><Label text="Shipping Class" /><SelectInput value={form.shippingClass} onChange={(value) => updateForm("shippingClass", value)} options={["Standard Parcel", "Bulky", "Express", "Pickup Only"]} /></div>
                  <div><Label text="Dispatch Time" /><SelectInput value={form.dispatchTime} onChange={(value) => updateForm("dispatchTime", value)} options={["Same day", "1-2 business days", "3-5 business days", "Pre-order"]} /></div>
                </Grid2>
                <Block><Label text="Shipping Notes" /><TextArea value={form.shippingNotes} onChange={(value) => updateForm("shippingNotes", value)} placeholder="Add packaging notes, delivery limitations, or special handling instructions." /></Block>
              </div>
            )}

            {activeTab === 5 && (
              <div className="space-y-8">
                <Grid4>
                  <div><Label text="Product Type" /><SelectInput value={form.productType} onChange={(value) => updateForm("productType", value)} options={["Physical", "Digital", "Service", "Bundle"]} /></div>
                  <div><Label text="Condition" /><SelectInput value={form.condition} onChange={(value) => updateForm("condition", value)} options={["New", "Refurbished", "Used"]} /></div>
                  <div><Label text="Visibility" /><SelectInput value={form.visibility} onChange={(value) => updateForm("visibility", value)} options={["Published", "Draft", "Hidden"]} /></div>
                  <div><Label text="Warranty" /><SelectInput value={form.warranty} onChange={(value) => updateForm("warranty", value)} options={["No warranty", "7 days", "30 days", "6 months", "1 year"]} /></div>
                </Grid4>
                <ToggleRow label="Featured Product" description="Highlight this item in featured storefront collections." enabled={form.featured} onToggle={() => updateForm("featured", !form.featured)} />
                <ToggleRow label="Returnable" description="Allow the product to be included in return and refund workflows." enabled={form.returnable} onToggle={() => updateForm("returnable", !form.returnable)} />
              </div>
            )}

            {activeTab === 6 && (
              <div className="space-y-8">
                <Block><Label text="Meta Title" /><TextInput value={form.seoTitle} onChange={(value) => updateForm("seoTitle", value)} placeholder="SEO title for search engines" /></Block>
                <Block><Label text="Meta Description" /><TextArea value={form.seoDescription} onChange={(value) => updateForm("seoDescription", value)} placeholder="A concise search snippet for search engines." /></Block>
                <Block><Label text="Meta Keywords" /><TextInput value={form.seoKeywords} onChange={(value) => updateForm("seoKeywords", value)} placeholder="keyword 1, keyword 2, keyword 3" /></Block>
              </div>
            )}
          </div>
        </section>

        {message ? (
          <div
            className={`mt-4 rounded-md border px-4 py-3 text-sm font-medium ${
              messageTone === "success"
                ? "border-[#bbf7d0] bg-[#f0fdf4] text-[#166534]"
                : "border-[#fecaca] bg-[#fef2f2] text-[#b91c1c]"
            }`}
          >
            {message}
          </div>
        ) : null}
      </div>

      <div className="sticky bottom-0 z-10 mt-8 border-t border-gray-200 bg-white/95 px-4 py-5 backdrop-blur-md md:px-8">
        <div className="mx-auto flex max-w-[1220px] items-center justify-between gap-3">
          <Link href="/admin/products" className="text-sm font-bold text-gray-500 hover:text-gray-900">Back to products</Link>
          <div className="flex items-center gap-3">
            <button type="button" onClick={() => void handleSubmit("draft")} disabled={isSubmitting || isLoadingProduct} className="inline-flex h-11 items-center justify-center rounded-md bg-gray-100 px-6 text-sm font-bold text-gray-700 transition hover:bg-gray-200 disabled:opacity-50">{isSubmitting ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : null}{editId ? "UPDATE & UNPUBLISH" : "SAVE & UNPUBLISH"}</button>
            <button type="button" onClick={() => void handleSubmit("publish")} disabled={isSubmitting || isLoadingProduct} className="inline-flex h-11 items-center justify-center rounded-md bg-[#1f2937] px-8 text-sm font-bold tracking-wide text-white transition hover:bg-black shadow-sm disabled:opacity-50">{isSubmitting ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : null}{editId ? "UPDATE & PUBLISH" : "SAVE & PUBLISH"}</button>
          </div>
        </div>
      </div>
    </div>
  );
}

function ProductTabs({ activeTab, setActiveTab }: { activeTab: number; setActiveTab: (idx: number) => void }) {
  return <div className="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm"><div className="flex min-w-max items-center gap-1.5 px-3 py-2.5">{tabs.map((tab, index) => <button key={tab} type="button" onClick={() => setActiveTab(index)} className={`rounded-md px-5 py-2.5 text-sm font-semibold transition ${index === activeTab ? "bg-indigo-50 text-[#0b63ce]" : "text-gray-500 hover:bg-gray-50 hover:text-gray-900"}`}>{tab}</button>)}</div></div>;
}

function Label({ text, required }: { text: string; required?: boolean }) {
  return <label className="mb-2 block text-sm font-bold text-gray-700">{text}{required ? <span className="ml-1 text-red-500">*</span> : null}</label>;
}

function TextInput({ placeholder, type = "text", value, onChange }: { placeholder?: string; type?: string; value: string; onChange: (value: string) => void }) {
  return <input type={type} placeholder={placeholder} value={value} onChange={(event) => onChange(event.target.value)} className="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce] focus:ring-0 placeholder:text-gray-400" />;
}

function TextArea({ placeholder, rows = 5, value, onChange }: { placeholder?: string; rows?: number; value: string; onChange: (value: string) => void }) {
  return <textarea rows={rows} value={value} onChange={(event) => onChange(event.target.value)} placeholder={placeholder} className="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce] focus:ring-0 placeholder:text-gray-400" />;
}

function SelectInput({ value, muted, onChange, options }: { value: string; muted?: boolean; onChange: (value: string) => void; options: SelectOption[] }) {
  return <div className="relative group"><select value={value} onChange={(event) => onChange(event.target.value)} className={`h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm outline-none transition group-hover:border-gray-400 focus:border-[#0b63ce] focus:ring-0 ${muted ? "text-gray-400" : "text-gray-700"}`}>{options.map((option) => <option key={typeof option === "string" ? option : option.value} value={typeof option === "string" ? option : option.value}>{typeof option === "string" ? option : option.label}</option>)}</select><ChevronDown className="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 transition group-hover:text-gray-600" /></div>;
}

function HierarchicalSelect({
  value,
  muted,
  onChange,
  options,
}: {
  value: string;
  muted?: boolean;
  onChange: (value: string) => void;
  options: SelectOption[];
}) {
  const [open, setOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    if (!open) return;

    const handlePointerDown = (event: MouseEvent) => {
      if (!containerRef.current?.contains(event.target as Node)) {
        setOpen(false);
      }
    };

    window.addEventListener("pointerdown", handlePointerDown);
    return () => window.removeEventListener("pointerdown", handlePointerDown);
  }, [open]);

  const selectedOption = options.find((option) => (typeof option === "string" ? option : option.value) === value);
  const selectedLabel = typeof selectedOption === "string"
    ? selectedOption
    : selectedOption?.label || value;

  return (
    <div ref={containerRef} className="relative">
      <button
        type="button"
        onClick={() => setOpen((current) => !current)}
        className={`flex h-11 w-full items-center justify-between rounded-md border border-gray-300 bg-white px-4 text-left text-sm outline-none transition hover:border-gray-400 focus:border-[#0b63ce] ${
          muted ? "text-gray-400" : "text-gray-700"
        }`}
      >
        <span className="truncate">{selectedLabel}</span>
        <ChevronDown className={`h-4 w-4 text-gray-400 transition ${open ? "rotate-180" : ""}`} />
      </button>

      {open ? (
        <div className="absolute left-0 right-0 top-[calc(100%+6px)] z-20 max-h-72 overflow-y-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg">
          {options.map((option) => {
            const normalized = typeof option === "string" ? { value: option, label: option, level: 0 } : option;
            const isSelected = normalized.value === value;

            return (
              <button
                key={normalized.value}
                type="button"
                onClick={() => {
                  onChange(normalized.value);
                  setOpen(false);
                }}
                className={`block w-full px-4 py-2 text-left text-sm transition hover:bg-gray-50 ${
                  normalized.level === 0 ? "font-semibold text-gray-900" : "font-normal text-gray-700"
                } ${isSelected ? "bg-[#f8fbff] text-[#0b63ce]" : ""}`}
              >
                {normalized.label}
              </button>
            );
          })}
        </div>
      ) : null}
    </div>
  );
}

function ToggleRow({ label, description, enabled, onToggle }: { label: string; description: string; enabled: boolean; onToggle: () => void }) {
  return <div className="flex items-start gap-4 py-2"><div className="flex h-11 items-center"><button type="button" aria-label={`Toggle ${label}`} onClick={onToggle} className={`relative inline-flex h-6 w-11 items-center rounded-full transition ${enabled ? "bg-[#0b63ce]" : "bg-gray-200"}`}><span className={`inline-block h-5 w-5 transform rounded-full bg-white transition ${enabled ? "translate-x-5" : "translate-x-1"}`} /></button></div><div><div className="mb-0.5 text-sm font-bold text-gray-700">{label}</div><p className="text-sm text-gray-500">{description}</p></div></div>;
}

function TextField({ label, value, onChange, placeholder, type = "text" }: { label: string; value: string; onChange: (value: string) => void; placeholder?: string; type?: string }) {
  return <div><Label text={label} /><TextInput value={value} onChange={onChange} placeholder={placeholder} type={type} /></div>;
}

function EmptyState({ text }: { text: string }) {
  return <div className="rounded-xl border border-gray-200 bg-gray-50 px-6 py-10 text-center text-sm text-gray-500">{text}</div>;
}

function Block({ children }: { children: React.ReactNode }) {
  return <div className="max-w-4xl">{children}</div>;
}

function Grid2({ children }: { children: React.ReactNode }) {
  return <div className="grid max-w-4xl grid-cols-1 gap-6 md:grid-cols-2">{children}</div>;
}

function Grid3({ children }: { children: React.ReactNode }) {
  return <div className="grid max-w-4xl gap-6 md:grid-cols-3">{children}</div>;
}

function Grid4({ children }: { children: React.ReactNode }) {
  return <div className="grid max-w-4xl gap-6 md:grid-cols-4">{children}</div>;
}

function updateVariant(setter: React.Dispatch<React.SetStateAction<VariantItem[]>>, id: string, patch: Partial<VariantItem>) {
  setter((current) => current.map((variant) => variant.id === id ? { ...variant, ...patch } : variant));
}

function updateSpec(setter: React.Dispatch<React.SetStateAction<SpecItem[]>>, id: string, patch: Partial<SpecItem>) {
  setter((current) => current.map((spec) => spec.id === id ? { ...spec, ...patch } : spec));
}

function slugify(value: string) {
  return value.toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
}

function buildSpecs(form: FormState, specs: SpecItem[]) {
  return [
    ...specs,
    { id: "unit", label: "Unit", value: form.unit },
    { id: "minimum_order_quantity", label: "Minimum Order Quantity", value: form.minimumOrderQuantity },
    { id: "barcode", label: "Barcode", value: form.barcode },
    { id: "tags", label: "Tags", value: form.tags },
    { id: "tax_class", label: "Tax Class", value: form.taxClass },
    { id: "low_stock_threshold", label: "Low Stock Alert", value: form.lowStockThreshold },
    { id: "product_type", label: "Product Type", value: form.productType },
    { id: "condition", label: "Condition", value: form.condition },
    { id: "visibility", label: "Visibility", value: form.visibility },
    { id: "warranty", label: "Warranty", value: form.warranty },
    { id: "shipping_notes", label: "Shipping Notes", value: form.shippingNotes },
    { id: "seo_title", label: "SEO Title", value: form.seoTitle },
    { id: "seo_description", label: "SEO Description", value: form.seoDescription },
    { id: "seo_keywords", label: "SEO Keywords", value: form.seoKeywords },
  ].filter((spec) => spec.label.trim() && spec.value.trim()).map(({ label, value }) => ({ label, value }));
}

const SYSTEM_SPEC_LABELS = new Set([
  "Unit",
  "Minimum Order Quantity",
  "Barcode",
  "Tags",
  "Tax Class",
  "Low Stock Alert",
  "Product Type",
  "Condition",
  "Visibility",
  "Warranty",
  "Shipping Notes",
  "SEO Title",
  "SEO Description",
  "SEO Keywords",
]);
