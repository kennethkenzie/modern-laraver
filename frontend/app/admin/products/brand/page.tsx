"use client";

import { Search, Pencil, Trash2, Image as ImageIcon, Loader2, X, Plus, Save, CheckCircle2 } from "lucide-react";
import { useState, useRef, useEffect } from "react";
import { useFrontendData } from "@/lib/use-frontend-data";
import { writeFrontendData } from "@/lib/frontend-data-store";
import { Brand } from "@/lib/frontend-data";

function Toggle({ enabled = true, onToggle }: { enabled?: boolean; onToggle?: () => void }) {
    return (
        <button
            type="button"
            onClick={onToggle}
            aria-pressed={enabled}
            className={`relative inline-flex h-6 w-12 items-center rounded-full transition ${enabled ? "bg-[#114f8f]" : "bg-slate-300"
                }`}
        >
            <span
                className={`inline-block h-5 w-5 transform rounded-full bg-white shadow transition ${enabled ? "translate-x-6" : "translate-x-1"
                    }`}
            />
        </button>
    );
}

function ImagePlaceholder({ className = "", src = "" }: { className?: string; src?: string }) {
    if (src) {
        return (
            <div className={`relative flex items-center justify-center rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden ${className}`}>
                <img src={src} alt="Preview" className="h-full w-full object-contain p-2" />
            </div>
        );
    }
    return (
        <div className={`flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-gray-50 text-slate-400 gap-2 ${className}`}>
            <ImageIcon className="h-8 w-8 opacity-50" strokeWidth={1.5} />
            <span className="text-[10px] font-black uppercase tracking-widest leading-none">No Media</span>
        </div>
    );
}

export default function BrandsPage() {
    const { data, isLoading } = useFrontendData();
    const brands = Array.isArray(data?.brands) ? data.brands : [];

    const [isSaving, setIsSaving] = useState(false);
    const [uploadingLogo, setUploadingLogo] = useState(false);
    const [uploadingBanner, setUploadingBanner] = useState(false);
    const [showSuccessToast, setShowSuccessToast] = useState(false);

    const [formData, setFormData] = useState({
        title: "",
        slug: "",
        logo: "",
        banner: "",
        metaTitle: "",
        metaDescription: "",
        isFeatured: false,
    });

    const [mounted, setMounted] = useState(false);
    useEffect(() => {
        setMounted(true);
    }, []);

    useEffect(() => {
        if (!showSuccessToast) return;

        const timeout = window.setTimeout(() => {
            setShowSuccessToast(false);
        }, 3200);

        return () => window.clearTimeout(timeout);
    }, [showSuccessToast]);

    const logoInputRef = useRef<HTMLInputElement>(null);
    const bannerInputRef = useRef<HTMLInputElement>(null);

    if (isLoading) {
        return (
            <div className="flex h-[600px] flex-col items-center justify-center rounded-[32px] bg-white border-2 border-dashed border-gray-100 shadow-sm transition-all animate-pulse">
                <Loader2 className="h-10 w-10 animate-spin text-[#114f8f] opacity-20" />
                <p className="mt-4 text-[11px] font-black uppercase tracking-widest text-gray-400">Synchronizing Brands...</p>
            </div>
        );
    }

    const handleUpload = async (file: File, type: "logo" | "banner") => {
        if (type === "logo") setUploadingLogo(true);
        else setUploadingBanner(true);

        try {
            const upData = new FormData();
            upData.append("file", file);

            const res = await fetch("/api/upload", {
                method: "POST",
                body: upData,
            });

            const raw = await res.text();
            let result: { url?: string; error?: string } = {};

            if (raw) {
                try {
                    result = JSON.parse(raw) as { url?: string; error?: string };
                } catch {
                    result = { error: raw };
                }
            }

            if (!res.ok) {
                throw new Error(result.error || `Upload failed with status ${res.status}`);
            }

            if (result.url) {
                setFormData(prev => ({ ...prev, [type]: result.url }));
            } else {
                alert("Upload failed: " + (result.error || "Unknown error"));
            }
        } catch (err) {
            console.error(err);
            alert(err instanceof Error ? err.message : "Upload failed");
        } finally {
            if (type === "logo") setUploadingLogo(false);
            else setUploadingBanner(false);
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!formData.title) return alert("Title is required");
        if (!formData.logo) return alert("Logo is required");

        setIsSaving(true);
        try {
            const newBrand: Brand = {
                id: crypto.randomUUID(),
                title: formData.title,
                slug: formData.slug || formData.title.toLowerCase().replace(/ /g, "-"),
                logo: formData.logo,
                banner: formData.banner,
                metaTitle: formData.metaTitle,
                metaDescription: formData.metaDescription,
                isActive: true,
                isFeatured: formData.isFeatured,
            };

            const updatedData = {
                ...data,
                brands: [newBrand, ...brands],
            };

            await writeFrontendData(updatedData);

            // Reset form
            setFormData({
                title: "",
                slug: "",
                logo: "",
                banner: "",
                metaTitle: "",
                metaDescription: "",
                isFeatured: false,
            });
            setShowSuccessToast(true);
        } catch (err) {
            console.error(err);
            alert("Failed to save brand.");
        } finally {
            setIsSaving(false);
        }
    };

    const toggleFeatured = async (id: string) => {
        const nextBrands = brands.map(b => b.id === id ? { ...b, isFeatured: !b.isFeatured } : b);
        await writeFrontendData({ ...data, brands: nextBrands });
    };

    const deleteBrand = async (id: string) => {
        if (!confirm("Are you sure you want to delete this brand?")) return;
        const nextBrands = brands.filter(b => b.id !== id);
        await writeFrontendData({ ...data, brands: nextBrands });
    };

    return (
        <div className="bg-[#f8fbff] min-h-screen">
            <div className="mb-10 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div className="flex items-start gap-4">
                    <span className="mt-2 h-2.5 w-10 rounded-full bg-[#f6c400] shadow-sm shadow-yellow-500/20" />
                    <div>
                        <h1 className="text-[32px] font-black tracking-tight text-gray-900 uppercase leading-none">Brand Management</h1>
                        <p className="mt-2 text-sm font-bold text-gray-400 uppercase tracking-[0.15em]">
                            You have {mounted ? brands.length : "..."} active {brands.length === 1 ? "Brand" : "Brands"}
                        </p>
                    </div>
                </div>

                {showSuccessToast ? (
                    <div className="flex min-w-0 items-start gap-3 rounded-2xl border border-[#d5e8d4] bg-gradient-to-r from-white via-[#f8fbf8] to-[#edf7ed] px-4 py-3 shadow-[0_14px_32px_rgba(15,23,42,0.08)] animate-in fade-in slide-in-from-top-4 xl:max-w-[420px]">
                        <div className="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#067d62] text-white shadow-sm">
                            <CheckCircle2 className="h-5 w-5" />
                        </div>
                        <div className="min-w-0">
                            <p className="text-[11px] font-black uppercase tracking-[0.22em] text-[#067d62]">Catalog updated</p>
                            <p className="mt-1 text-sm font-bold leading-5 text-[#0f1111]">
                                Brand added successfully and ready for storefront use.
                            </p>
                        </div>
                    </div>
                ) : null}
            </div>

            <div className="grid grid-cols-1 gap-8 xl:grid-cols-[1.3fr_0.8fr]">
                {/* Brand List Section */}
                <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 className="text-xl font-black text-gray-900 uppercase tracking-tight">Existing Brands</h2>
                            <p className="text-[11px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Database Listing</p>
                        </div>

                        <div className="flex w-full max-w-[360px] items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-2.5">
                            <Search className="h-4 w-4 text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search brands..."
                                className="w-full bg-transparent text-sm font-medium outline-none placeholder:text-gray-400"
                            />
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead>
                                <tr className="border-b border-gray-100 bg-gray-50/50 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">
                                    <th className="px-6 py-5">Position</th>
                                    <th className="px-6 py-5">Branding</th>
                                    <th className="px-6 py-5">Title</th>
                                    <th className="px-6 py-5">Visibility</th>
                                    <th className="px-6 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {brands.map((brand, idx) => (
                                    <tr key={brand.id} className="group transition-colors hover:bg-gray-50/40">
                                        <td className="px-6 py-5 font-bold text-gray-400">{idx + 1}</td>
                                        <td className="px-6 py-5">
                                            <div className="h-10 w-10 overflow-hidden rounded-xl border border-gray-100 bg-white p-1 shadow-sm">
                                                <img src={brand.logo} alt={brand.title} className="h-full w-full object-contain" />
                                            </div>
                                        </td>
                                        <td className="px-6 py-5">
                                            <div className="font-bold text-gray-900">{brand.title}</div>
                                            <div className="text-[11px] font-medium text-gray-500 uppercase tracking-wider mt-0.5">{brand.slug}</div>
                                        </td>
                                        <td className="px-6 py-5">
                                            <div className="flex items-center gap-2">
                                                <Toggle enabled={brand.isFeatured} onToggle={() => toggleFeatured(brand.id)} />
                                                <span className={`text-[11px] font-black uppercase tracking-widest ${brand.isFeatured ? "text-[#114f8f]" : "text-gray-400"}`}>
                                                    {brand.isFeatured ? "Featured" : "Regular"}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-5 text-right">
                                            <div className="flex justify-end gap-2">
                                                <button className="flex h-9 w-9 items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-[#114f8f] hover:border-[#114f8f] transition-all active:scale-95 shadow-sm">
                                                    <Pencil className="h-4 w-4" />
                                                </button>
                                                <button onClick={() => deleteBrand(brand.id)} className="flex h-9 w-9 items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-500 transition-all active:scale-95 shadow-sm">
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>

                {/* Add/Edit Form Section */}
                <section className="self-start rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-gray-200 px-6 py-5">
                         <h2 className="text-xl font-black text-gray-900 uppercase tracking-tight">Configuration</h2>
                         <p className="text-[11px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Brand Details</p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6 px-6 py-8">
                        <div>
                            <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">Brand Title</label>
                            <input
                                value={formData.title}
                                onChange={e => setFormData(prev => ({ ...prev, title: e.target.value }))}
                                placeholder="e.g. Samsung"
                                className="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"
                            />
                        </div>

                        <div className="grid gap-6 sm:grid-cols-2">
                             <div>
                                <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">URL Slug</label>
                                <input
                                    value={formData.slug}
                                    onChange={e => setFormData(prev => ({ ...prev, slug: e.target.value }))}
                                    placeholder="brand-name"
                                    className="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"
                                />
                             </div>
                             <div className="flex flex-col justify-end pb-1.5">
                                <div className="flex items-center gap-3">
                                    <Toggle enabled={formData.isFeatured} onToggle={() => setFormData(p => ({ ...p, isFeatured: !p.isFeatured }))} />
                                    <span className="text-[11px] font-black uppercase tracking-widest text-gray-500">Featured</span>
                                </div>
                             </div>
                        </div>

                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">Main Logo</label>
                                <input
                                    type="file"
                                    className="hidden"
                                    accept="image/*"
                                    ref={logoInputRef}
                                    onChange={e => e.target.files?.[0] && handleUpload(e.target.files[0], "logo")}
                                />
                                <div className="relative group">
                                    <ImagePlaceholder className="h-32 w-full" src={formData.logo} />
                                    <button
                                        type="button"
                                        onClick={() => logoInputRef.current?.click()}
                                        disabled={uploadingLogo}
                                        className="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl text-white font-bold text-xs gap-2"
                                    >
                                        {uploadingLogo ? <Loader2 className="h-4 w-4 animate-spin" /> : <Plus className="h-4 w-4" />}
                                        {uploadingLogo ? "Uploading..." : "Upload Logo"}
                                    </button>
                                    {formData.logo && (
                                        <button
                                            type="button"
                                            onClick={() => setFormData(prev => ({ ...prev, logo: "" }))}
                                            className="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-lg hover:bg-red-600 transition-colors z-10"
                                        >
                                            <X className="h-3.5 w-3.5" />
                                        </button>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">Banner (Small)</label>
                                <input
                                    type="file"
                                    className="hidden"
                                    accept="image/*"
                                    ref={bannerInputRef}
                                    onChange={e => e.target.files?.[0] && handleUpload(e.target.files[0], "banner")}
                                />
                                <div className="relative group">
                                    <ImagePlaceholder className="h-32 w-full" src={formData.banner} />
                                    <button
                                        type="button"
                                        onClick={() => bannerInputRef.current?.click()}
                                        disabled={uploadingBanner}
                                        className="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl text-white font-bold text-xs gap-2"
                                    >
                                        {uploadingBanner ? <Loader2 className="h-4 w-4 animate-spin" /> : <Plus className="h-4 w-4" />}
                                        {uploadingBanner ? "Uploading..." : "Upload Banner"}
                                    </button>
                                    {formData.banner && (
                                        <button
                                            type="button"
                                            onClick={() => setFormData(prev => ({ ...prev, banner: "" }))}
                                            className="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-lg hover:bg-red-600 transition-colors z-10"
                                        >
                                            <X className="h-3.5 w-3.5" />
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="space-y-4 pt-4 border-t border-gray-100">
                             <p className="text-[11px] font-black uppercase tracking-widest text-[#114f8f]">SEO & Marketing</p>
                            <div>
                                <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">Meta Title</label>
                                <input
                                    value={formData.metaTitle}
                                    onChange={e => setFormData(prev => ({ ...prev, metaTitle: e.target.value }))}
                                    placeholder="Search Engine Optimized Title"
                                    className="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"
                                />
                            </div>

                            <div>
                                <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">Meta Description</label>
                                <textarea
                                    value={formData.metaDescription}
                                    onChange={e => setFormData(prev => ({ ...prev, metaDescription: e.target.value }))}
                                    placeholder="Brief summary for search results..."
                                    className="min-h-[100px] w-full rounded-xl border border-gray-300 px-4 py-3 text-sm font-medium text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"
                                />
                            </div>
                        </div>

                        <div className="flex items-center justify-end pt-4 gap-4 border-t border-gray-100">
                            <button
                                type="submit"
                                disabled={isSaving || uploadingLogo || uploadingBanner}
                                className="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-[#114f8f] text-[15px] font-black uppercase tracking-wide text-white transition-all hover:bg-[#0d3f74] disabled:opacity-50 min-w-[200px] shadow-lg shadow-blue-900/10"
                            >
                                {isSaving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save size={18} />}
                                {isSaving ? "Saving Database" : "Save Brand Details"}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    );
}
