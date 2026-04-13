"use client";

import {
    Search,
    Pencil,
    Trash2,
    Image as ImageIcon,
    ChevronLeft,
    ChevronRight,
    ChevronDown,
    Loader2
} from "lucide-react";
import { useState, useRef, useEffect } from "react";
import { useFrontendData } from "@/lib/use-frontend-data";
import { writeFrontendData } from "@/lib/frontend-data-store";
import { Category } from "@/lib/frontend-data";

type CategoryFormData = {
    title: string;
    rootCategory: string;
    order: string;
    slug: string;
    commission: string;
    thumbnail: string;
    banner: string;
    icon: string;
    isFeatured: boolean;
    isActive: boolean;
};

export default function CategoryPage() {
    const { data, isLoading } = useFrontendData();
    const categories = Array.isArray(data?.categories) ? data.categories : [];

    const [isSaving, setIsSaving] = useState(false);
    const [saveNotice, setSaveNotice] = useState<string | null>(null);
    const [uploadingThumb, setUploadingThumb] = useState(false);
    const [uploadingBanner, setUploadingBanner] = useState(false);
    const [uploadingIcon, setUploadingIcon] = useState(false);
    const [editingCategoryId, setEditingCategoryId] = useState<string | null>(null);
    const [searchQuery, setSearchQuery] = useState("");

    const [formData, setFormData] = useState<CategoryFormData>({
        title: "",
        rootCategory: "",
        order: "0",
        slug: "",
        commission: "0",
        thumbnail: "",
        banner: "",
        icon: "",
        isFeatured: false,
        isActive: true,
    });

    const resetForm = () => {
        setFormData({
            title: "",
            rootCategory: "",
            order: "0",
            slug: "",
            commission: "0",
            thumbnail: "",
            banner: "",
            icon: "",
            isFeatured: false,
            isActive: true,
        });
        setEditingCategoryId(null);
    };

    const [mounted, setMounted] = useState(false);
    useEffect(() => {
        setMounted(true);
    }, []);

    useEffect(() => {
        if (!saveNotice) return;
        const timeout = window.setTimeout(() => {
            setSaveNotice(null);
        }, 3200);
        return () => window.clearTimeout(timeout);
    }, [saveNotice]);

    if (isLoading) {
        return (
            <div className="flex h-[600px] flex-col items-center justify-center rounded-[32px] bg-white border-2 border-dashed border-gray-100 shadow-sm transition-all animate-pulse">
                <Loader2 className="h-10 w-10 animate-spin text-[#114f8f] opacity-20" />
                <p className="mt-4 text-[11px] font-black uppercase tracking-widest text-gray-400">Synchronizing Categories...</p>
            </div>
        );
    }

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!formData.title) return alert("Title is required");
        if (!data) return alert("Data store not initialized");

        setIsSaving(true);
        try {
            const nextCategory: Category = {
                id: editingCategoryId || crypto.randomUUID(),
                title: formData.title,
                rootCategory: formData.rootCategory || undefined,
                order: parseInt(formData.order) || 0,
                commission: formData.commission + " %",
                isFeatured: formData.isFeatured,
                isActive: formData.isActive,
                thumbnail: formData.thumbnail,
                banner: formData.banner,
                icon: formData.icon,
                slug: formData.slug || formData.title.toLowerCase().replace(/\s+/g, "-"),
            };

            const updatedCategories = editingCategoryId
                ? categories.map((category) =>
                    category.id === editingCategoryId ? nextCategory : category
                )
                : [nextCategory, ...categories];

            await writeFrontendData({ ...data, categories: updatedCategories });

            const wasEditing = Boolean(editingCategoryId);
            resetForm();
            setSaveNotice(
                wasEditing
                    ? "Category updated successfully."
                    : "Category added successfully."
            );
        } catch (error) {
            console.error("Save error:", error);
            alert("An error occurred. Please try again.");
        } finally {
            setIsSaving(false);
        }
    };

    const handleUpload = async (file: File, type: "thumbnail" | "banner" | "icon") => {
        if (type === "thumbnail") setUploadingThumb(true);
        else if (type === "banner") setUploadingBanner(true);
        else setUploadingIcon(true);

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

            if (!res.ok) throw new Error(result.error || "Upload failed");

            if (result.url) {
                setFormData(prev => ({ ...prev, [type]: result.url }));
            }
        } catch (error) {
            console.error("Upload error:", error);
            alert("Failed to upload image. Please try again.");
        } finally {
            if (type === "thumbnail") setUploadingThumb(false);
            else if (type === "banner") setUploadingBanner(false);
            else setUploadingIcon(false);
        }
    };

    const handleToggleStatus = async (id: string) => {
        if (!data) return;
        const updated = categories.map(cat =>
            cat.id === id ? { ...cat, isActive: !cat.isActive } : cat
        );
        await writeFrontendData({ ...data, categories: updated });
    };

    const handleToggleFeatured = async (id: string) => {
        if (!data) return;
        const updated = categories.map(cat =>
            cat.id === id ? { ...cat, isFeatured: !cat.isFeatured } : cat
        );
        await writeFrontendData({ ...data, categories: updated });
    };

    const handleDelete = async (id: string) => {
        if (!data) return;
        if (!confirm("Are you sure you want to delete this category?")) return;
        const updated = categories.filter(cat => cat.id !== id);
        await writeFrontendData({ ...data, categories: updated });
        if (editingCategoryId === id) {
            resetForm();
        }
    };

    const handleEdit = (category: Category) => {
        setEditingCategoryId(category.id);
        setFormData({
            title: category.title,
            rootCategory: category.rootCategory || "",
            order: String(category.order ?? 0),
            slug: category.slug || "",
            commission: category.commission.replace(/\s*%$/, ""),
            thumbnail: category.thumbnail || "",
            banner: category.banner || "",
            icon: category.icon || "",
            isFeatured: Boolean(category.isFeatured),
            isActive: Boolean(category.isActive),
        });
    };

    const filteredCategories = categories.filter((cat) => {
        const query = searchQuery.toLowerCase();
        return (
            cat.title.toLowerCase().includes(query) ||
            cat.slug?.toLowerCase().includes(query) ||
            cat.rootCategory?.toLowerCase().includes(query)
        );
    });

    const pages = [1];

    return (
        <div className="bg-[#f7f7f8] p-4 sm:p-6 lg:p-8">
            {saveNotice ? (
                <div className="mb-5 flex items-center justify-between gap-4 rounded-2xl border border-emerald-200 bg-[linear-gradient(135deg,#ecfdf5_0%,#f0fdf4_55%,#ffffff_100%)] px-5 py-4 shadow-[0_18px_40px_rgba(16,185,129,0.12)]">
                    <div>
                        <p className="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-600">
                            Saved
                        </p>
                        <p className="mt-1 text-sm font-semibold text-gray-900">
                            {saveNotice}
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={() => setSaveNotice(null)}
                        className="inline-flex h-9 items-center justify-center rounded-full border border-emerald-200 bg-white px-4 text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-50"
                    >
                        Dismiss
                    </button>
                </div>
            ) : null}

            <div className="mb-6 flex items-start gap-3">
                <span className="mt-2 h-2.5 w-8 rounded-full bg-[#0b63ce]" />
                <div>
                    <h1 className="text-[28px] font-semibold tracking-tight text-gray-900">
                        All Categories
                    </h1>
                    <p className="mt-1 text-sm text-gray-500">
                        You have a total of {mounted ? categories.length : "..."} Categories
                    </p>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.9fr_0.92fr]">
                <section className="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-gray-100 bg-gray-50/30 px-5 py-4 md:flex-row md:items-center md:justify-between">
                        <h2 className="text-lg font-bold text-gray-900">
                            Categories
                        </h2>

                        <div className="flex w-full max-w-[320px] overflow-hidden rounded-md border border-gray-300 bg-white transition hover:border-gray-400">
                            <input
                                type="text"
                                placeholder="Search categories..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="h-11 w-full border-0 bg-transparent px-4 text-sm text-gray-700 outline-none placeholder:text-gray-400"
                            />
                            <button
                                type="button"
                                className="flex h-11 w-14 items-center justify-center bg-[#1f2937] text-white hover:bg-black transition-colors"
                            >
                                <Search className="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead>
                                <tr className="border-b border-gray-200 bg-gray-50 text-left text-gray-600">
                                    <th className="px-5 py-4 font-semibold text-gray-900">#</th>
                                    <th className="px-5 py-4 font-semibold text-gray-900">Title</th>
                                    <th className="px-5 py-4 font-semibold text-gray-900 whitespace-nowrap">Root Category</th>
                                    <th className="px-5 py-4 font-semibold text-gray-900 text-center">Order</th>
                                    <th className="px-5 py-4 font-semibold text-gray-900 text-center">Thumb</th>
                                    <th className="px-5 py-4 font-semibold text-gray-900 text-center">Banner</th>
                                    <th className="px-5 py-4 font-semibold text-gray-900 text-center">Icon</th>
                                    <th className="px-5 py-4 font-semibold text-gray-900">Comm.</th>
                                    <th className="px-5 py-4 font-semibold text-gray-900 text-center">Featured</th>
                                    <th className="px-5 py-4 font-semibold text-gray-900 text-center">Status</th>
                                    <th className="px-5 py-4 font-semibold text-gray-900 text-right">Options</th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-gray-100">
                                {filteredCategories.length === 0 ? (
                                    <tr>
                                        <td colSpan={11} className="px-5 py-10 text-center text-gray-400 font-medium">
                                            {searchQuery ? `No categories matching "${searchQuery}"` : "No categories available. Please add a new category."}
                                        </td>
                                    </tr>
                                ) : (
                                    filteredCategories.map((category, idx) => (
                                        <tr
                                            key={category.id}
                                            className="hover:bg-gray-50/50 transition-colors"
                                        >
                                            <td className="px-5 py-4 font-medium text-gray-500">{idx + 1}</td>
                                            <td className="px-5 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                                {category.title}
                                            </td>
                                            <td className="px-5 py-4 text-gray-600 whitespace-nowrap">
                                                {category.rootCategory || "-"}
                                            </td>
                                            <td className="px-5 py-4 text-center text-gray-600 font-medium">{category.order}</td>
                                            <td className="px-5 py-3 text-center">
                                                <ImageThumbnail src={category.thumbnail} className="mx-auto" />
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                <ImageThumbnail src={category.banner} className="mx-auto w-12" />
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                <ImageThumbnail src={category.icon} className="mx-auto" />
                                            </td>
                                            <td className="px-5 py-4 text-gray-600 font-medium whitespace-nowrap">
                                                {category.commission}
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                <Toggle enabled={category.isFeatured} onToggle={() => handleToggleFeatured(category.id)} />
                                            </td>
                                            <td className="px-5 py-3 text-center">
                                                <Toggle enabled={category.isActive} onToggle={() => handleToggleStatus(category.id)} />
                                            </td>
                                            <td className="px-5 py-3 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <IconButton variant="edit" onClick={() => handleEdit(category)}>
                                                        <Pencil className="h-4 w-4" />
                                                    </IconButton>
                                                    <IconButton variant="delete" onClick={() => handleDelete(category.id)}>
                                                        <Trash2 className="h-4 w-4" />
                                                    </IconButton>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="flex flex-wrap items-center gap-1.5 px-5 py-6">
                        <PaginationArrow>
                            <ChevronLeft className="h-4 w-4" />
                        </PaginationArrow>

                        {pages.map((page) => (
                            <button
                                key={page}
                                type="button"
                                className={`h-9 min-w-9 rounded-md border text-sm font-semibold transition ${page === 1
                                    ? "bg-[#0b63ce] border-[#0b63ce] text-white"
                                    : "bg-white border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-900"
                                    }`}
                            >
                                {page}
                            </button>
                        ))}

                        <PaginationArrow>
                            <ChevronRight className="h-4 w-4" />
                        </PaginationArrow>
                    </div>
                </section>

                <section className="rounded-lg border border-gray-200 bg-white shadow-sm self-start sticky top-6">
                    <div className="border-b border-gray-100 bg-gray-50/30 px-5 py-4">
                        <h2 className="text-lg font-bold text-gray-900">
                            {editingCategoryId ? "Edit Category" : "Add New Category"}
                        </h2>
                    </div>

                    <form className="space-y-6 px-5 py-6" onSubmit={handleSubmit}>
                        <div>
                            <Label text="Title" required />
                            <TextInput
                                placeholder="e.g. Smartphones"
                                value={formData.title}
                                onChange={(val) => setFormData(prev => ({ ...prev, title: val }))}
                            />
                        </div>

                        <div>
                            <Label text="Root Category" />
                            <SelectInput
                                value={formData.rootCategory || "Select Root Category"}
                                onChange={(val) => setFormData(prev => ({ ...prev, rootCategory: val === "Select Root Category" ? "" : val }))}
                                options={["Select Root Category", ...categories.map(c => c.title)]}
                                muted={!formData.rootCategory}
                            />
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-bold text-gray-700">
                                Order{" "}
                                <span className="text-[12px] font-medium text-gray-400">
                                    (To show on menu sidebar)
                                </span>
                            </label>
                            <TextInput
                                placeholder="e.g. 1"
                                type="number"
                                value={formData.order}
                                onChange={(val) => setFormData(prev => ({ ...prev, order: val }))}
                            />
                        </div>

                        <div>
                            <Label text="Slug" />
                            <TextInput
                                placeholder="smartphones"
                                value={formData.slug}
                                onChange={(val) => setFormData(prev => ({ ...prev, slug: val }))}
                            />
                        </div>

                        <div>
                            <Label text="Commission Rate (%)" />
                            <TextInput
                                placeholder="0"
                                type="number"
                                value={formData.commission}
                                onChange={(val) => setFormData(prev => ({ ...prev, commission: val }))}
                            />
                        </div>

                        <div>
                            <Label text="Icon" />
                            <FileInput
                                onFileSelect={(file) => handleUpload(file, "icon")}
                                isUploading={uploadingIcon}
                                currentFile={formData.icon}
                            />
                            {formData.icon && (
                                <div className="mt-4 relative inline-block group">
                                    <img src={formData.icon} alt="Icon" className="h-20 w-20 object-cover rounded-md border border-gray-200" />
                                    <button
                                        type="button"
                                        onClick={() => setFormData(prev => ({ ...prev, icon: "" }))}
                                        className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-sm"
                                    >
                                        <Trash2 className="h-3 w-3" />
                                    </button>
                                </div>
                            )}
                        </div>

                        <div>
                            <Label text="Thumbnail (72*72)" />
                            <FileInput
                                onFileSelect={(file) => handleUpload(file, "thumbnail")}
                                isUploading={uploadingThumb}
                                currentFile={formData.thumbnail}
                            />
                            {formData.thumbnail && (
                                <div className="mt-4 relative inline-block group">
                                    <img src={formData.thumbnail} alt="Thumbnail" className="h-20 w-20 object-cover rounded-md border border-gray-200" />
                                    <button
                                        type="button"
                                        onClick={() => setFormData(prev => ({ ...prev, thumbnail: "" }))}
                                        className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-sm"
                                    >
                                        <Trash2 className="h-3 w-3" />
                                    </button>
                                </div>
                            )}
                        </div>

                        <div>
                            <Label text="Banner (835*200)" />
                            <FileInput
                                onFileSelect={(file) => handleUpload(file, "banner")}
                                isUploading={uploadingBanner}
                                currentFile={formData.banner}
                            />
                            {formData.banner && (
                                <div className="mt-4 relative group">
                                    <img src={formData.banner} alt="Banner" className="h-24 w-full object-cover rounded-md border border-gray-200" />
                                    <button
                                        type="button"
                                        onClick={() => setFormData(prev => ({ ...prev, banner: "" }))}
                                        className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-sm"
                                    >
                                        <Trash2 className="h-3 w-3" />
                                    </button>
                                </div>
                            )}
                        </div>

                        <div className="flex justify-end gap-3 pt-4">
                            {editingCategoryId ? (
                                <button
                                    type="button"
                                    onClick={resetForm}
                                    className="inline-flex h-11 items-center justify-center rounded-md border border-gray-300 bg-white px-6 text-sm font-bold tracking-wide text-gray-700 transition hover:bg-gray-50"
                                >
                                    CANCEL
                                </button>
                            ) : null}
                            <button
                                type="submit"
                                disabled={isSaving || uploadingThumb || uploadingBanner || uploadingIcon}
                                className="inline-flex h-11 items-center justify-center rounded-md bg-[#1f2937] px-8 text-sm font-bold tracking-wide text-white transition hover:bg-black shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {isSaving ? "SAVING..." : editingCategoryId ? "UPDATE CATEGORY" : "SAVE CATEGORY"}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    );
}

function Label({
    text,
    required,
}: {
    text: string;
    required?: boolean;
}) {
    return (
        <label className="mb-2 block text-sm font-bold text-gray-700">
            {text}
            {required && <span className="ml-1 text-red-500">*</span>}
        </label>
    );
}

function TextInput({
    placeholder,
    type = "text",
    value,
    onChange
}: {
    placeholder?: string;
    type?: string;
    value?: string;
    onChange?: (val: string) => void;
}) {
    return (
        <input
            type={type}
            placeholder={placeholder}
            value={value}
            onChange={(e) => onChange?.(e.target.value)}
            className="h-11 w-full rounded-md border border-gray-300 bg-white px-4 text-sm text-gray-700 outline-none transition hover:border-gray-400 focus:border-[#0b63ce] focus:ring-0 placeholder:text-gray-400"
        />
    );
}

function SelectInput({
    value,
    muted,
    onChange,
    options
}: {
    value: string;
    muted?: boolean;
    onChange?: (val: string) => void;
    options: string[];
}) {
    return (
        <div className="relative group">
            <select
                value={value}
                onChange={(e) => onChange?.(e.target.value)}
                className={`h-11 w-full appearance-none rounded-md border border-gray-300 bg-white px-4 pr-10 text-sm outline-none transition group-hover:border-gray-400 focus:border-[#0b63ce] focus:ring-0 ${muted ? "text-gray-400" : "text-gray-700"
                    }`}
            >
                {options.map(opt => (
                    <option key={opt} value={opt}>{opt}</option>
                ))}
            </select>
            <ChevronDown className="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 group-hover:text-gray-600 transition-colors" />
        </div>
    );
}

function FileInput({
    onFileSelect,
    isUploading,
    currentFile
}: {
    onFileSelect: (file: File) => void;
    isUploading?: boolean;
    currentFile?: string;
}) {
    const inputRef = useRef<HTMLInputElement>(null);

    return (
        <div className="overflow-hidden rounded-md border border-gray-300 bg-white group hover:border-gray-400 transition-colors">
            <input
                type="file"
                className="hidden"
                ref={inputRef}
                accept="image/*"
                onChange={(e) => {
                    const file = e.target.files?.[0];
                    if (file) onFileSelect(file);
                }}
            />
            <div className="flex items-center">
                <div className="flex h-11 flex-1 items-center px-4 text-sm text-gray-400 truncate">
                    {isUploading ? "Uploading..." : currentFile ? "Image uploaded" : "No file chosen"}
                </div>
                <button
                    type="button"
                    onClick={() => inputRef.current?.click()}
                    disabled={isUploading}
                    className="h-11 border-l border-gray-200 bg-gray-50 px-5 text-sm font-bold text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-50"
                >
                    Choose File
                </button>
            </div>
        </div>
    );
}

function Toggle({ enabled = false, onToggle }: { enabled?: boolean; onToggle?: () => void }) {
    return (
        <button
            type="button"
            onClick={onToggle}
            aria-pressed={enabled}
            className={`relative inline-flex h-6 w-12 items-center rounded-full transition ${enabled ? "bg-[#0b63ce]" : "bg-gray-200"
                }`}
        >
            <span
                className={`inline-block h-5 w-5 rounded-full bg-white shadow-sm transition ${enabled ? "translate-x-6" : "translate-x-1"
                    }`}
            />
        </button>
    );
}

function ImageThumbnail({ src, className = "" }: { src?: string; className?: string }) {
    if (src) {
        return (
            <div className={`h-9 w-9 rounded border border-gray-200 bg-gray-50 p-1 ${className}`}>
                <img src={src} alt="Category" className="h-full w-full object-contain" />
            </div>
        );
    }
    return (
        <div
            className={`flex h-9 w-9 items-center justify-center rounded border border-gray-200 bg-gray-50 text-gray-300 ${className}`}
        >
            <ImageIcon className="h-5 w-5" strokeWidth={1.8} />
        </div>
    );
}

function IconButton({
    children,
    variant,
    onClick
}: {
    children: React.ReactNode;
    variant: "edit" | "delete";
    onClick?: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`flex h-8 w-8 items-center justify-center rounded-md transition ${variant === "edit"
                ? "bg-gray-100 text-gray-600 hover:bg-gray-200"
                : "bg-red-50 text-red-500 hover:bg-red-100"
                }`}
        >
            {children}
        </button>
    );
}

function PaginationArrow({ children }: { children: React.ReactNode }) {
    return (
        <button
            type="button"
            className="flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-400 hover:bg-gray-50 transition-colors"
        >
            {children}
        </button>
    );
}
