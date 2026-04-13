"use client";

import { useEffect, useRef, useState } from "react";
import {
  ChevronDown,
  ChevronUp,
  Loader2,
  Plus,
  Save,
  Trash2,
  Upload,
  Image as ImageIcon,
  CheckCircle2,
  RefreshCcw,
  ShieldCheck,
} from "lucide-react";
import { useFrontendData } from "@/lib/use-frontend-data";
import { writeFrontendData } from "@/lib/frontend-data-store";
import type { NavQuickLink, NavTopLink, NavbarData } from "@/lib/frontend-data";

type HeaderSectionProps = {
  title: string;
  description: string;
  children: React.ReactNode;
  defaultOpen?: boolean;
};

type UploadResponse = {
  url?: string;
};

const topLinkIcons: NavTopLink["icon"][] = ["home", "info", "mail"];

function HeaderSection({
  title,
  description,
  children,
  defaultOpen = true,
}: HeaderSectionProps) {
  const [open, setOpen] = useState(defaultOpen);

  return (
    <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md">
      <button
        type="button"
        onClick={() => setOpen((value) => !value)}
        className="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
      >
        <div>
          <h2 className="text-xl font-black text-[#111827] uppercase tracking-tight">{title}</h2>
          <p className="mt-1 text-sm font-bold text-gray-400 uppercase tracking-widest">{description}</p>
        </div>
        <span className={`inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-50 text-gray-400 transition-transform duration-300 ${open ? "rotate-180" : ""}`}>
          <ChevronDown className="h-5 w-5" />
        </span>
      </button>

      {open ? <div className="border-t border-gray-100 px-6 py-8">{children}</div> : null}
    </section>
  );
}

function Field({
  label,
  value,
  onChange,
  placeholder,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
}) {
  return (
    <label className="block">
      <span className="mb-2 block text-[12px] font-black uppercase tracking-[0.15em] text-gray-400">{label}</span>
      <input
        value={value || ""}
        onChange={(event) => onChange(event.target.value)}
        placeholder={placeholder}
        className="h-12 w-full rounded-2xl border border-gray-200 bg-gray-50/30 px-5 text-sm font-bold text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50/50"
      />
    </label>
  );
}

export default function HeaderSettingsPage() {
  const { data, isLoading } = useFrontendData();
  const [nav, setNav] = useState<NavbarData | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [isUploading, setIsUploading] = useState(false);
  const [showSavedToast, setShowSavedToast] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const faviconInputRef = useRef<HTMLInputElement>(null);

  // Sync internal state with loaded data
  useEffect(() => {
    if (!isLoading && data?.navbar && !nav) {
      setNav(JSON.parse(JSON.stringify(data.navbar)));
    }
  }, [data, nav, isLoading]);

  if (isLoading || !nav) {
    return (
      <div className="flex h-[400px] flex-col items-center justify-center rounded-[32px] bg-white border-2 border-dashed border-gray-100 shadow-sm">
        <Loader2 className="h-10 w-10 animate-spin text-[#114f8f] opacity-20" />
        <p className="mt-4 text-[11px] font-black uppercase tracking-widest text-gray-400">Synchronizing Storefront...</p>
      </div>
    );
  }

  const handleUpdate = async () => {
    if (!nav) return;
    setIsSaving(true);
    try {
      await writeFrontendData({
        ...data,
        navbar: nav,
      });
      setShowSavedToast(true);
      setTimeout(() => setShowSavedToast(false), 3000);
    } catch (error) {
      console.error(error);
      alert("Database error: could not write settings.");
    } finally {
      setIsSaving(false);
    }
  };

  const updateTopLink = (index: number, patch: Partial<NavTopLink>) => {
    setNav((prev) => {
      if (!prev) return null;
      const nextTopLinks = prev.topLinks.map((link, i) =>
        i === index ? { ...link, ...patch } : link
      );
      return { ...prev, topLinks: nextTopLinks };
    });
  };

  const updateQuickLink = (index: number, patch: Partial<NavQuickLink>) => {
    setNav((prev) => {
      if (!prev) return null;
      const nextQuickLinks = prev.quickLinks.map((link, i) =>
        i === index ? { ...link, ...patch } : link
      );
      return { ...prev, quickLinks: nextQuickLinks };
    });
  };

  const addTopLink = () => {
    setNav((prev) => {
      if (!prev) return null;
      return {
        ...prev,
        topLinks: [...prev.topLinks, { label: "New link", href: "/", icon: "home" }],
      };
    });
  };

  const removeTopLink = (index: number) => {
    setNav((prev) => {
      if (!prev) return null;
      return {
        ...prev,
        topLinks: prev.topLinks.filter((_, i) => i !== index),
      };
    });
  };

  const addQuickLink = () => {
    if (nav.quickLinks.length >= 4) {
      alert("Main navigation supports up to 4 items max for this template.");
      return;
    }
    setNav((prev) => {
      if (!prev) return null;
      return {
        ...prev,
        quickLinks: [...prev.quickLinks, { label: "New menu item", href: "/" }],
      };
    });
  };

  const removeQuickLink = (index: number) => {
    setNav((prev) => {
      if (!prev) return null;
      return {
        ...prev,
        quickLinks: prev.quickLinks.filter((_, i) => i !== index),
      };
    });
  };

  const updateNavbarMeta = (patch: Partial<NavbarData>) => {
    setNav((prev) => (prev ? { ...prev, ...patch } : null));
  };

   const handleLogoUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    setIsUploading(true);
    try {
      const formData = new FormData();
      formData.append("file", file);

      const response = await fetch("/api/upload", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) throw new Error("Upload failed");

      const result: UploadResponse = await response.json();
      if (result.url) {
        updateNavbarMeta({ logoUrl: result.url });
      }
    } catch (error) {
      console.error("Logo upload error:", error);
      alert("Failed to upload logo.");
    } finally {
      setIsUploading(false);
      if (fileInputRef.current) fileInputRef.current.value = "";
    }
  };

  const handleFaviconUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    setIsUploading(true);
    try {
      const formData = new FormData();
      formData.append("file", file);

      const response = await fetch("/api/upload", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) throw new Error("Upload failed");

      const result: UploadResponse = await response.json();
      if (result.url) {
        updateNavbarMeta({ faviconUrl: result.url });
      }
    } catch (error) {
      console.error("Favicon upload error:", error);
      alert("Failed to upload favicon.");
    } finally {
      setIsUploading(false);
      if (faviconInputRef.current) faviconInputRef.current.value = "";
    }
  };

  const SaveButton = ({ label }: { label: string }) => (
    <div className="mt-10 flex items-center justify-end border-t border-gray-100 pt-8">
        <button
            onClick={handleUpdate}
            disabled={isSaving}
            className="inline-flex h-14 items-center gap-3 rounded-2xl bg-[#114f8f] px-10 text-[15px] font-black uppercase tracking-wide text-white transition-all hover:bg-[#0d3f74] disabled:opacity-50 shadow-xl shadow-blue-900/10 active:scale-95"
        >
            {isSaving ? <Loader2 size={18} className="animate-spin" /> : <Save size={18} />}
            {isSaving ? "Syncing..." : label}
        </button>
    </div>
  );

  return (
    <div className="bg-[#f8fbff] min-h-screen">
      {/* Page Header */}
      <div className="mb-12 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div className="flex items-start gap-4">
          <span className="mt-2 h-2.5 w-10 rounded-full bg-[#f6c400] shadow-sm shadow-yellow-500/20" />
          <div>
            <h1 className="text-[36px] font-black tracking-tighter text-[#111827] uppercase leading-none">Header Architect</h1>
            <p className="mt-2 text-[11px] font-black text-gray-400 uppercase tracking-[0.25em]">
              Global Branding & Search Experience
            </p>
          </div>
        </div>

        <div className="flex items-center gap-3">
            {showSavedToast && (
                <div className="flex items-center gap-2 text-[13px] font-black text-emerald-600 bg-emerald-50 px-6 py-3 rounded-2xl border border-emerald-100 shadow-sm animate-in fade-in slide-in-from-top-6 uppercase tracking-wider">
                    <CheckCircle2 size={18} />
                    Configuration Synchronized
                </div>
            )}
        </div>
      </div>

      <div className="space-y-10">
        <HeaderSection
          title="Branding & Tab Settings"
          description="Global site identity as seen in browser tabs and search engines."
        >
          <div className="grid gap-8 md:grid-cols-2 pb-8 border-b border-gray-100 mb-8">
            <Field
              label="Site Title (SEO)"
              value={nav.siteTitle || ""}
              onChange={(value) => updateNavbarMeta({ siteTitle: value })}
              placeholder="e.g. Modern Electronics | Premium Electronics Store"
            />
            <label className="block">
                <span className="mb-2 block text-[12px] font-black uppercase tracking-[0.15em] text-gray-400">Favicon (Site Icon) URL</span>
                <div className="flex gap-2">
                  <input
                    value={nav.faviconUrl || ""}
                    onChange={(event) => updateNavbarMeta({ faviconUrl: event.target.value })}
                    placeholder="https://..."
                    className="h-12 w-full rounded-2xl border border-gray-200 bg-gray-50/30 px-5 text-sm font-bold text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50/50"
                  />
                  <input
                    type="file"
                    ref={faviconInputRef}
                    onChange={handleFaviconUpload}
                    accept="image/*,.ico,.svg"
                    className="hidden"
                  />
                  <button
                    type="button"
                    disabled={isUploading}
                    onClick={() => faviconInputRef.current?.click()}
                    className="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-white text-gray-500 transition-all hover:text-[#114f8f] hover:border-[#114f8f] disabled:opacity-50 shadow-sm active:scale-90"
                  >
                    {isUploading ? <Loader2 className="h-5 w-5 animate-spin" /> : <Upload className="h-5 w-5" />}
                  </button>
                </div>
            </label>
          </div>
          <div className="grid gap-12 lg:grid-cols-[1fr_320px]">
            <div className="flex flex-col gap-8">
              <label className="block">
                <span className="mb-2 block text-[12px] font-black uppercase tracking-[0.15em] text-gray-400">Logo Source URL</span>
                <div className="flex gap-2">
                  <input
                    value={nav.logoUrl}
                    onChange={(event) => updateNavbarMeta({ logoUrl: event.target.value })}
                    placeholder="https://cloudinary.com/..."
                    className="h-12 w-full rounded-2xl border border-gray-200 bg-gray-50/30 px-5 text-sm font-bold text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50/50"
                  />
                  <input
                    type="file"
                    ref={fileInputRef}
                    onChange={handleLogoUpload}
                    accept="image/*,.svg"
                    className="hidden"
                  />
                  <button
                    type="button"
                    disabled={isUploading}
                    onClick={() => fileInputRef.current?.click()}
                    className="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-gray-200 bg-white text-gray-500 transition-all hover:text-[#114f8f] hover:border-[#114f8f] disabled:opacity-50 shadow-sm active:scale-90"
                  >
                    {isUploading ? <Loader2 className="h-5 w-5 animate-spin" /> : <Upload className="h-5 w-5" />}
                  </button>
                </div>
              </label>

              <Field
                label="Accessibility (Alt) Text"
                value={nav.logoAlt}
                onChange={(value) => updateNavbarMeta({ logoAlt: value })}
                placeholder="Describe the logo for screen readers"
              />

              <Field
                label="Global Search Placeholder"
                value={nav.searchPlaceholder}
                onChange={(value) => updateNavbarMeta({ searchPlaceholder: value })}
                placeholder="What are they searching for?"
              />
            </div>

            <div className="flex flex-col">
              <span className="mb-2 block text-[12px] font-black uppercase tracking-[0.15em] text-gray-400">Visual Preview</span>
              <div className="relative flex flex-1 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-8 transition-all hover:border-[#114f8f]/30">
                {nav.logoUrl ? (
                  <div className="relative flex h-32 w-full items-center justify-center p-4">
                    <img
                      src={nav.logoUrl}
                      key={nav.logoUrl}
                      alt={nav.logoAlt || "Logo preview"}
                      className="h-full w-auto max-w-full object-contain drop-shadow-sm"
                      onError={(e) => {
                        e.currentTarget.style.display = 'none';
                        e.currentTarget.parentElement?.querySelector('.error-msg')?.classList.remove('hidden');
                      }}
                    />
                    <div className="error-msg hidden flex flex-col items-center gap-2 text-red-400">
                        <RefreshCcw size={24} className="animate-pulse" />
                        <span className="text-[10px] font-black uppercase tracking-widest">URL Broken</span>
                    </div>
                  </div>
                ) : (
                  <div className="flex flex-col items-center gap-3 text-gray-400 opacity-40">
                    <div className="h-16 w-16 bg-white rounded-full flex items-center justify-center border border-gray-100 shadow-sm">
                        <ImageIcon className="h-8 w-8" />
                    </div>
                    <span className="text-[11px] font-black uppercase tracking-widest leading-none">Blank Canvas</span>
                  </div>
                )}
                
                {nav.logoUrl && (
                    <div className="absolute bottom-4 rounded-full bg-[#114f8f]/10 px-4 py-1 text-[9px] font-black uppercase tracking-widest text-[#114f8f]">
                         Active Render
                    </div>
                )}
              </div>
            </div>
          </div>
          <SaveButton label="Synchronize Identity" />
        </HeaderSection>

        <HeaderSection
          title="Utility Banner"
          description="Smaller functional links visible at the top of the viewport."
        >
          <div className="space-y-6">
            {nav.topLinks.map((link, index) => (
              <div
                key={`${link.href}-${index}`}
                className="rounded-2xl border border-gray-100 bg-gray-50/30 p-8 transition-all hover:bg-white hover:shadow-md"
              >
                <div className="mb-8 flex items-center justify-between gap-3">
                  <div className="flex items-center gap-4">
                    <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-white border border-gray-200 text-[#114f8f] font-black text-[16px] shadow-sm">
                        {index + 1}
                    </div>
                    <div>
                        <div className="text-[18px] font-black text-[#111827] uppercase tracking-tight">Utility Link</div>
                        <div className="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1 underline decoration-[#f6c400] decoration-2 underline-offset-4">Placement configuration</div>
                    </div>
                  </div>
                  <button
                    type="button"
                    onClick={() => removeTopLink(index)}
                    className="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white border border-gray-100 text-red-500 transition-all hover:bg-red-50 hover:border-red-100 shadow-sm active:scale-90"
                    aria-label={`Remove utility link ${index + 1}`}
                  >
                    <Trash2 className="h-5 w-5" />
                  </button>
                </div>

                <div className="grid gap-8 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_200px]">
                  <Field
                    label="Label"
                    value={link.label}
                    onChange={(value) => updateTopLink(index, { label: value })}
                    placeholder="e.g. Find Store"
                  />
                  <Field
                    label="Destination Path"
                    value={link.href}
                    onChange={(value) => updateTopLink(index, { href: value })}
                    placeholder="/stores"
                  />
                  <label className="block">
                    <span className="mb-2 block text-[12px] font-black uppercase tracking-[0.15em] text-gray-400">Library Icon</span>
                    <select
                      value={link.icon}
                      onChange={(event) =>
                        updateTopLink(index, {
                          icon: event.target.value as NavTopLink["icon"],
                        })
                      }
                      className="h-12 w-full rounded-2xl border border-gray-200 bg-white px-5 text-sm font-black text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50/50"
                    >
                      {topLinkIcons.map((icon) => (
                        <option key={icon} value={icon}>
                          {icon.toUpperCase()}
                        </option>
                      ))}
                    </select>
                  </label>
                </div>
              </div>
            ))}
          </div>

          <div className="mt-10">
            <button
              type="button"
              onClick={addTopLink}
              className="inline-flex items-center gap-3 rounded-2xl bg-[#f6c400] px-8 py-4 text-[13px] font-black text-[#111827] shadow-xl shadow-yellow-500/15 transition-all hover:bg-yellow-400 active:scale-95 border border-yellow-300/20 uppercase tracking-widest"
            >
              <Plus className="h-5 w-5" />
              Append New Link
            </button>
          </div>
          <SaveButton label="Update Utility Bar" />
        </HeaderSection>

        <HeaderSection
          title="Principal Navigation"
          description="The main menu architecture for cross-category movement."
        >
          <div className="mb-10 rounded-2xl border-2 border-dashed border-[#114f8f]/10 bg-[#114f8f]/5 p-8">
            <div className="flex gap-5">
                <ShieldCheck size={28} className="text-[#114f8f] shrink-0" />
                <div>
                   <h4 className="text-[14px] font-black text-[#114f8f] uppercase tracking-wider">Navigation Strategy</h4>
                   <p className="mt-1 text-sm font-medium text-[#114f8f]/70 leading-relaxed">
                       Current UI layout supports up to 4 items. Use Slot 3 for "Daily Deals" to leverage the gold highlight effect. Slot 4 is ideal for B2B or Wholesale portals.
                   </p>
                </div>
            </div>
          </div>

          <div className="space-y-6">
            {nav.quickLinks.map((link, index) => (
              <div
                key={`${link.href}-${index}`}
                className="rounded-2xl border border-gray-100 bg-gray-50/30 p-8 transition-all hover:bg-white hover:shadow-md"
              >
                <div className="mb-8 flex items-center justify-between gap-3">
                   <div className="flex items-center gap-4">
                    <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-white border border-gray-200 text-[#114f8f] font-black text-[16px] shadow-sm">
                        {index + 1}
                    </div>
                    <div>
                        <div className="text-[18px] font-black text-[#111827] uppercase tracking-tight">Main Channel</div>
                        <div className="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1.5 leading-none">
                            {index === 2
                                ? "Promotional Highlighting Active"
                                : index === 3
                                  ? "Primary Action Threshold"
                                  : "Core Navigation Link"}
                        </div>
                    </div>
                  </div>
                  <button
                    type="button"
                    onClick={() => removeQuickLink(index)}
                    className="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white border border-gray-100 text-red-500 transition-all hover:bg-red-50 hover:border-red-100 shadow-sm active:scale-90"
                    aria-label={`Remove menu item ${index + 1}`}
                  >
                    <Trash2 className="h-5 w-5" />
                  </button>
                </div>

                <div className="grid gap-8 md:grid-cols-2">
                  <Field
                    label="Visible Label"
                    value={link.label}
                    onChange={(value) => updateQuickLink(index, { label: value })}
                    placeholder="e.g. Shop All"
                  />
                  <Field
                    label="Navigation Route"
                    value={link.href}
                    onChange={(value) => updateQuickLink(index, { href: value })}
                    placeholder="/inventory"
                  />
                </div>
              </div>
            ))}
          </div>

          <div className="mt-10">
            <button
              type="button"
              onClick={addQuickLink}
              className="inline-flex items-center gap-3 rounded-2xl bg-[#f6c400] px-8 py-4 text-[13px] font-black text-[#111827] shadow-xl shadow-yellow-500/15 transition-all hover:bg-yellow-400 active:scale-95 border border-yellow-300/20 uppercase tracking-widest"
            >
              <Plus className="h-5 w-5" />
              Append Menu Item
            </button>
          </div>
          <SaveButton label="Update Header Architecture" />
        </HeaderSection>
      </div>
    </div>
  );
}
