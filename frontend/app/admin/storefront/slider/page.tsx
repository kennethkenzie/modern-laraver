"use client";

import { useRef, useState, useEffect } from "react";
import Link from "next/link";
import {
  Image as ImageIcon,
  Loader2,
  Pencil,
  Plus,
  Trash2,
  X,
  Save,
  CheckCircle2,
  ExternalLink,
} from "lucide-react";
import { useFrontendData } from "@/lib/use-frontend-data";
import { writeFrontendData } from "@/lib/frontend-data-store";
import type { HeroSlide } from "@/lib/frontend-data";

type SliderFormData = {
  image: string;
  title: string;
  description: string;
  buttonText: string;
  buttonLink: string;
};

const emptyForm: SliderFormData = {
  image: "",
  title: "",
  description: "",
  buttonText: "",
  buttonLink: "",
};

export default function SliderPage() {
  const { data, isLoading } = useFrontendData();
  const slides = Array.isArray(data?.hero?.slides) ? data.hero.slides : [];

  const [formData, setFormData] = useState<SliderFormData>(emptyForm);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [isUploading, setIsUploading] = useState(false);
  const [showToast, setShowToast] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  const [mounted, setMounted] = useState(false);
  useEffect(() => {
    setMounted(true);
  }, []);

  if (isLoading) {
    return (
      <div className="flex h-[400px] flex-col items-center justify-center rounded-[32px] bg-white border-2 border-dashed border-gray-100 shadow-sm">
        <Loader2 className="h-10 w-10 animate-spin text-[#114f8f] opacity-20" />
        <p className="mt-4 text-[11px] font-black uppercase tracking-widest text-gray-400">Loading Slide Configuration...</p>
      </div>
    );
  }

  const resetForm = () => {
    setFormData(emptyForm);
    setEditingId(null);
  };

  const handleUpload = async (file: File) => {
    setIsUploading(true);

    try {
      const body = new FormData();
      body.append("file", file);

      const response = await fetch("/api/upload", {
        method: "POST",
        body,
      });

      const raw = await response.text();
      let payload: { url?: string; error?: string } = {};

      if (raw) {
        try {
          payload = JSON.parse(raw) as { url?: string; error?: string };
        } catch {
          payload = { error: raw };
        }
      }

      if (!response.ok) {
        throw new Error(payload.error || "Upload failed.");
      }

      if (payload.url) {
        setFormData((prev) => ({ ...prev, image: payload.url || "" }));
      }
    } catch (error) {
      console.error(error);
      alert(error instanceof Error ? error.message : "Upload failed.");
    } finally {
      setIsUploading(false);
    }
  };

  const handleEdit = (slide: HeroSlide) => {
    setEditingId(slide.id);
    setFormData({
      image: slide.image || "",
      title: slide.title || "",
      description: slide.description || "",
      buttonText: slide.ctaLabel || "",
      buttonLink: slide.ctaHref || "",
    });
    // Scroll to form on mobile
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this hero slide?")) return;

    const nextSlides = slides.filter((slide) => slide.id !== id);
    await writeFrontendData({
      ...data,
      hero: {
        ...data.hero,
        slides: nextSlides,
      },
    });

    if (editingId === id) {
      resetForm();
    }
  };

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();

    if (!formData.image || !formData.title || !formData.buttonText || !formData.buttonLink) {
      alert("Please fill in all required fields (Image, Title, Button Text & Link).");
      return;
    }

    setIsSaving(true);

    try {
      const nextSlide: HeroSlide = {
        id: editingId || crypto.randomUUID(),
        image: formData.image,
        title: formData.title,
        description: formData.description,
        ctaLabel: formData.buttonText,
        ctaHref: formData.buttonLink,
      };

      const nextSlides = editingId
        ? slides.map((slide) => (slide.id === editingId ? nextSlide : slide))
        : [...slides, nextSlide];

      await writeFrontendData({
        ...data,
        hero: {
          ...data.hero,
          slides: nextSlides,
        },
      });

      resetForm();
      setShowToast(true);
      setTimeout(() => setShowToast(false), 3000);
    } catch (error) {
      console.error(error);
      alert("Failed to save slide.");
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <div className="bg-[#f8fbff] min-h-screen">
      <div className="mb-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div className="flex items-start gap-4">
          <span className="mt-2 h-2.5 w-10 rounded-full bg-[#f6c400] shadow-sm shadow-yellow-500/20" />
          <div>
            <h1 className="text-[32px] font-black tracking-tight text-gray-900 uppercase">Hero Slider</h1>
            <p className="mt-1 text-sm font-bold text-gray-400 uppercase tracking-widest leading-none">Banner Management System</p>
          </div>
        </div>

        {showToast && (
            <div className="flex items-center gap-2 text-[13px] font-black text-emerald-600 bg-emerald-50 px-5 py-3 rounded-2xl border border-emerald-100 shadow-sm animate-in fade-in slide-in-from-top-4 uppercase tracking-wider">
                <CheckCircle2 size={18} />
                Slider Updated Successfully
            </div>
        )}
      </div>

      <div className="grid grid-cols-1 gap-8 xl:grid-cols-[1.5fr_1fr]">
        {/* Slides List Section */}
        <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
          <div className="flex items-center justify-between border-b border-gray-100 px-6 py-5">
            <div>
                 <h2 className="text-xl font-black text-gray-900 uppercase tracking-tight leading-none">Active Slides</h2>
                 <p className="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mt-2 underline decoration-[#f6c400] decoration-2 underline-offset-4">{slides.length} PLACEMENTS TOTAL</p>
            </div>
            <button
                onClick={resetForm}
                className="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 border border-gray-200 text-[#114f8f] transition-all hover:bg-white active:scale-95"
            >
                <Plus size={20} />
            </button>
          </div>

          <div className="divide-y divide-gray-50">
            {slides.length === 0 ? (
              <div className="px-6 py-20 text-center flex flex-col items-center gap-4">
                  <div className="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300">
                      <ImageIcon size={32} />
                  </div>
                  <p className="text-sm font-black text-gray-400 uppercase tracking-widest">No hero slides found</p>
              </div>
            ) : (
              slides.map((slide, index) => (
                <div key={slide.id} className="group relative flex flex-col lg:flex-row gap-6 px-6 py-8 transition-colors hover:bg-gray-50/50">
                  <div className="relative h-[120px] w-full lg:w-[220px] shrink-0 overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 shadow-sm">
                    <img
                      src={slide.image}
                      alt={slide.title}
                      className="h-full w-full object-cover transition-transform group-hover:scale-105"
                    />
                    <div className="absolute left-3 top-3 rounded-lg bg-black/60 px-2 py-1 text-[10px] font-black text-white uppercase tracking-wider backdrop-blur-md">
                        {slide.id === editingId ? "Active Editing" : `Position ${index + 1}`}
                    </div>
                  </div>

                  <div className="min-w-0 flex-1">
                    <div className="mb-2 text-[11px] font-black uppercase tracking-[0.15em] text-[#114f8f]">
                      CTA Context: {slide.ctaLabel}
                    </div>
                    <div className="text-xl font-black text-[#111827] leading-tight line-clamp-1">{slide.title}</div>
                    <p className="mt-2 line-clamp-2 text-sm font-medium text-gray-500 leading-relaxed md:max-w-md">{slide.description}</p>
                    <div className="mt-4 flex flex-wrap items-center gap-4">
                      <div className="flex items-center gap-2 text-[12px] font-bold text-gray-400 group-hover:text-[#114f8f] transition-colors">
                        <ExternalLink size={14} />
                        <span className="truncate max-w-[120px]">{slide.ctaHref}</span>
                      </div>
                    </div>
                  </div>

                  <div className="flex items-start gap-2 lg:flex-col lg:justify-center">
                    <button
                      type="button"
                      onClick={() => handleEdit(slide)}
                      className="flex h-11 w-11 items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 transition-all hover:text-[#114f8f] hover:border-[#114f8f] active:scale-95 shadow-sm"
                    >
                      <Pencil className="h-4 w-4" />
                    </button>
                    <button
                      type="button"
                      onClick={() => handleDelete(slide.id)}
                      className="flex h-11 w-11 items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-400 transition-all hover:text-red-500 hover:border-red-500 active:scale-95 shadow-sm"
                    >
                      <Trash2 className="h-4 w-4" />
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>
        </section>

        {/* Configuration Form Section */}
        <section id="slide-form" className="self-start rounded-[24px] border border-gray-200 bg-white shadow-sm overflow-hidden sticky top-8">
          <div className="border-b border-gray-100 px-6 py-5 flex items-center justify-between">
            <div>
                 <h2 className="text-xl font-black text-gray-900 uppercase tracking-tight leading-none">
                    {editingId ? "Modify Slide" : "Initialize Slide"}
                 </h2>
                 <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2 leading-none">
                    {editingId ? "Updating existing placement" : "New marketing banner"}
                 </p>
            </div>
            {editingId && (
                <button 
                  onClick={resetForm} 
                  className="text-[11px] font-black uppercase text-red-500 hover:text-red-600 transition-colors"
                >
                    Discard Changes
                </button>
            )}
          </div>

          <form onSubmit={handleSubmit} className="space-y-6 px-6 py-8">
            <div className="space-y-4">
              <label className="block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">Hero Background Media</label>
              <input
                ref={inputRef}
                type="file"
                accept="image/*"
                className="hidden"
                onChange={(event) => {
                  const file = event.target.files?.[0];
                  if (file) void handleUpload(file);
                }}
              />

              <div className="group relative mt-2">
                {formData.image ? (
                  <div className="relative overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 shadow-sm">
                    <img src={formData.image} alt="Slider preview" className="h-[200px] w-full object-cover transition-transform group-hover:scale-105" />
                    <div className="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                         <div className="bg-white/90 backdrop-blur-md rounded-full h-12 w-12 flex items-center justify-center text-gray-900 shadow-xl">
                            <UploadCloud className="h-5 w-5" />
                         </div>
                    </div>
                    <button
                      type="button"
                      onClick={() => setFormData((prev) => ({ ...prev, image: "" }))}
                      className="absolute right-4 top-4 inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white shadow-xl hover:bg-red-600 transition-all active:scale-95 z-10"
                    >
                      <X className="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        onClick={() => inputRef.current?.click()}
                        className="absolute bottom-4 left-4 right-4 bg-white/90 backdrop-blur-md rounded-xl py-2.5 text-[11px] font-black uppercase tracking-widest text-[#111827] shadow-lg opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0"
                    >
                        Replace Image
                    </button>
                  </div>
                ) : (
                  <button
                    type="button"
                    onClick={() => inputRef.current?.click()}
                    disabled={isUploading}
                    className="flex h-[200px] w-full flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 text-gray-400 transition-all hover:bg-gray-100/50 hover:border-gray-300 group active:scale-[0.99]"
                  >
                    {isUploading ? <Loader2 className="h-8 w-8 animate-spin text-[#114f8f]" /> : <ImageIcon className="h-10 w-10 opacity-30 group-hover:scale-110 transition-transform" />}
                    <span className="mt-3 text-[11px] font-black uppercase tracking-[0.2em]">
                        {isUploading ? "Processing Content..." : "Drop Slider Media Here"}
                    </span>
                  </button>
                )}
              </div>
            </div>

            <Field
              label="Banner Headline"
              value={formData.title}
              onChange={(value) => setFormData((prev) => ({ ...prev, title: value }))}
              placeholder="e.g. Professional Repair Tools"
            />

            <Field
              label="Context Description"
              value={formData.description}
              onChange={(value) => setFormData((prev) => ({ ...prev, description: value }))}
              placeholder="Keep it around 2-3 lines for optimal layout."
              multiline
            />

            <div className="grid grid-cols-2 gap-4">
                <Field
                  label="Button Label"
                  value={formData.buttonText}
                  onChange={(value) => setFormData((prev) => ({ ...prev, buttonText: value }))}
                  placeholder="Shop Now"
                />

                <Field
                  label="Redirect URL"
                  value={formData.buttonLink}
                  onChange={(value) => setFormData((prev) => ({ ...prev, buttonLink: value }))}
                  placeholder="/inventory"
                />
            </div>

            <div className="flex flex-col gap-3 pt-4 border-t border-gray-100">
              <button
                type="submit"
                disabled={isSaving || isUploading}
                className="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#114f8f] px-8 text-sm font-black uppercase tracking-widest text-white transition-all hover:bg-[#0d3f74] disabled:cursor-not-allowed disabled:opacity-50 shadow-xl shadow-blue-900/10 active:scale-95"
              >
                {isSaving ? <Loader2 className="h-5 w-5 animate-spin" /> : <Save size={18} />}
                {isSaving ? "Saving Configuration..." : editingId ? "Force Update Slide" : "Save New Placement"}
              </button>
            </div>
          </form>
        </section>
      </div>
    </div>
  );
}

function Field({
  label,
  value,
  onChange,
  placeholder,
  multiline = false,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  multiline?: boolean;
}) {
  return (
    <div>
      <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">{label}</label>
      {multiline ? (
        <textarea
          value={value}
          onChange={(event) => onChange(event.target.value)}
          placeholder={placeholder}
          className="min-h-[100px] w-full rounded-xl border border-gray-300 px-4 py-3 text-sm font-medium text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"
        />
      ) : (
        <input
          value={value}
          onChange={(event) => onChange(event.target.value)}
          placeholder={placeholder}
          className="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"
        />
      )}
    </div>
  );
}

function UploadCloud({ className }: { className?: string }) {
    return (
        <svg 
            xmlns="http://www.w3.org/2000/svg" 
            width="24" height="24" 
            viewBox="0 0 24 24" 
            fill="none" 
            stroke="currentColor" 
            strokeWidth="2" 
            strokeLinecap="round" 
            strokeLinejoin="round" 
            className={className}
        >
            <path d="M17.5 19L9 19C6.23858 19 4 16.7614 4 14C4 11.2386 6.23858 9 9 9L9.7541 9C10.5126 5.63231 13.5113 3 17 3C20.866 3 24 6.13401 24 10C24 13.0645 22.0315 15.667 19.2936 16.6346" />
            <path d="M12 21L12 13" />
            <path d="M9 16L12 13L15 16" />
        </svg>
    );
}
