"use client";

import {
    Search,
    Pencil,
    Trash2,
    Loader2,
    Save,
    CheckCircle2,
    X,
    Plus,
    ChevronDown,
    ChevronUp,
    Layers,
    Tag,
} from "lucide-react";
import { useState, useEffect } from "react";

type AttributeOption = {
    id: string;
    value: string;
    colorHex?: string; // optional — used for color swatches
};

type AttributeSet = {
    id: string;
    name: string;
    inputType: "dropdown" | "text" | "color" | "radio" | "checkbox";
    options: AttributeOption[];
    isActive: boolean;
    createdAt: string;
};

const STORAGE_KEY = "admin_attribute_sets_v1";

const INPUT_TYPE_LABELS: Record<AttributeSet["inputType"], string> = {
    dropdown: "Dropdown",
    text:     "Text",
    color:    "Color Swatch",
    radio:    "Radio Button",
    checkbox: "Checkbox",
};

const DEFAULT_SETS: AttributeSet[] = [
    {
        id: "attr-1",
        name: "Color",
        inputType: "color",
        options: [
            { id: "c1", value: "Black",  colorHex: "#111827" },
            { id: "c2", value: "White",  colorHex: "#f9fafb" },
            { id: "c3", value: "Silver", colorHex: "#9ca3af" },
            { id: "c4", value: "Blue",   colorHex: "#3b82f6" },
            { id: "c5", value: "Red",    colorHex: "#ef4444" },
        ],
        isActive: true,
        createdAt: "2026-01-01",
    },
    {
        id: "attr-2",
        name: "Storage Capacity",
        inputType: "radio",
        options: [
            { id: "s1", value: "64 GB" },
            { id: "s2", value: "128 GB" },
            { id: "s3", value: "256 GB" },
            { id: "s4", value: "512 GB" },
            { id: "s5", value: "1 TB" },
        ],
        isActive: true,
        createdAt: "2026-01-01",
    },
    {
        id: "attr-3",
        name: "Screen Size",
        inputType: "dropdown",
        options: [
            { id: "sc1", value: "32 inch" },
            { id: "sc2", value: "43 inch" },
            { id: "sc3", value: "50 inch" },
            { id: "sc4", value: "55 inch" },
            { id: "sc5", value: "65 inch" },
            { id: "sc6", value: "75 inch" },
        ],
        isActive: true,
        createdAt: "2026-01-05",
    },
    {
        id: "attr-4",
        name: "Compatibility",
        inputType: "checkbox",
        options: [
            { id: "cp1", value: "Samsung" },
            { id: "cp2", value: "LG" },
            { id: "cp3", value: "Sony" },
            { id: "cp4", value: "Hisense" },
            { id: "cp5", value: "TCL" },
        ],
        isActive: false,
        createdAt: "2026-01-10",
    },
];

function loadSets(): AttributeSet[] {
    if (typeof window === "undefined") return DEFAULT_SETS;
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : DEFAULT_SETS;
    } catch {
        return DEFAULT_SETS;
    }
}

function saveSets(sets: AttributeSet[]) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(sets));
}

function Toggle({ enabled = false, onToggle }: { enabled?: boolean; onToggle?: () => void }) {
    return (
        <button
            type="button"
            onClick={onToggle}
            aria-pressed={enabled}
            className={`relative inline-flex h-6 w-12 items-center rounded-full transition ${enabled ? "bg-[#114f8f]" : "bg-gray-200"}`}
        >
            <span className={`inline-block h-5 w-5 transform rounded-full bg-white shadow transition ${enabled ? "translate-x-6" : "translate-x-1"}`} />
        </button>
    );
}

function InputTypeBadge({ type }: { type: AttributeSet["inputType"] }) {
    const colors: Record<AttributeSet["inputType"], string> = {
        dropdown: "bg-blue-50 text-blue-700 border-blue-200",
        text:     "bg-gray-100 text-gray-600 border-gray-200",
        color:    "bg-pink-50 text-pink-700 border-pink-200",
        radio:    "bg-green-50 text-green-700 border-green-200",
        checkbox: "bg-amber-50 text-amber-700 border-amber-200",
    };
    return (
        <span className={`inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-black uppercase tracking-widest ${colors[type]}`}>
            {INPUT_TYPE_LABELS[type]}
        </span>
    );
}

export default function AttributeSetsPage() {
    const [sets, setSets] = useState<AttributeSet[]>([]);
    const [mounted, setMounted] = useState(false);
    const [isSaving, setIsSaving] = useState(false);
    const [showToast, setShowToast] = useState<string | null>(null);
    const [searchQuery, setSearchQuery] = useState("");
    const [editingId, setEditingId] = useState<string | null>(null);
    const [expandedId, setExpandedId] = useState<string | null>(null);

    const [formData, setFormData] = useState<{
        name: string;
        inputType: AttributeSet["inputType"];
        isActive: boolean;
        options: AttributeOption[];
    }>({
        name: "",
        inputType: "dropdown",
        isActive: true,
        options: [{ id: crypto.randomUUID(), value: "", colorHex: "" }],
    });

    useEffect(() => {
        setSets(loadSets());
        setMounted(true);
    }, []);

    useEffect(() => {
        if (!showToast) return;
        const t = window.setTimeout(() => setShowToast(null), 3200);
        return () => window.clearTimeout(t);
    }, [showToast]);

    const resetForm = () => {
        setFormData({
            name: "",
            inputType: "dropdown",
            isActive: true,
            options: [{ id: crypto.randomUUID(), value: "", colorHex: "" }],
        });
        setEditingId(null);
    };

    const addOption = () => {
        setFormData(p => ({
            ...p,
            options: [...p.options, { id: crypto.randomUUID(), value: "", colorHex: "" }],
        }));
    };

    const updateOption = (id: string, field: keyof AttributeOption, value: string) => {
        setFormData(p => ({
            ...p,
            options: p.options.map(o => o.id === id ? { ...o, [field]: value } : o),
        }));
    };

    const removeOption = (id: string) => {
        setFormData(p => ({
            ...p,
            options: p.options.filter(o => o.id !== id),
        }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!formData.name.trim()) return alert("Attribute set name is required");
        const validOptions = formData.options.filter(o => o.value.trim());
        if (validOptions.length === 0) return alert("At least one option is required");

        setIsSaving(true);
        await new Promise(r => setTimeout(r, 300));

        const next: AttributeSet = {
            id: editingId || crypto.randomUUID(),
            name: formData.name.trim(),
            inputType: formData.inputType,
            isActive: formData.isActive,
            options: validOptions.map(o => ({ ...o, value: o.value.trim() })),
            createdAt: editingId
                ? sets.find(s => s.id === editingId)?.createdAt ?? new Date().toISOString().split("T")[0]
                : new Date().toISOString().split("T")[0],
        };

        const updated = editingId
            ? sets.map(s => s.id === editingId ? next : s)
            : [next, ...sets];

        setSets(updated);
        saveSets(updated);
        setShowToast(editingId ? "Attribute set updated." : "Attribute set created.");
        resetForm();
        setIsSaving(false);
    };

    const handleEdit = (set: AttributeSet) => {
        setEditingId(set.id);
        setFormData({
            name: set.name,
            inputType: set.inputType,
            isActive: set.isActive,
            options: set.options.length > 0
                ? set.options.map(o => ({ ...o }))
                : [{ id: crypto.randomUUID(), value: "", colorHex: "" }],
        });
        window.scrollTo({ top: 0, behavior: "smooth" });
    };

    const handleDelete = (id: string) => {
        if (!confirm("Delete this attribute set?")) return;
        const updated = sets.filter(s => s.id !== id);
        setSets(updated);
        saveSets(updated);
        if (editingId === id) resetForm();
        if (expandedId === id) setExpandedId(null);
    };

    const handleToggle = (id: string) => {
        const updated = sets.map(s => s.id === id ? { ...s, isActive: !s.isActive } : s);
        setSets(updated);
        saveSets(updated);
    };

    const filtered = sets.filter(s =>
        s.name.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
        <div className="space-y-8">
            {/* Header */}
            <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div className="flex items-start gap-4">
                    <span className="mt-2 h-2.5 w-10 rounded-full bg-[#f6c400] shadow-sm shadow-yellow-500/20" />
                    <div>
                        <h1 className="text-[32px] font-black tracking-tight text-gray-900 uppercase leading-none">
                            Attribute Sets
                        </h1>
                        <p className="mt-2 text-sm font-bold text-gray-400 uppercase tracking-[0.15em]">
                            {mounted ? sets.length : "..."} {sets.length === 1 ? "set" : "sets"} defined
                        </p>
                    </div>
                </div>

                {showToast && (
                    <div className="flex min-w-0 items-start gap-3 rounded-2xl border border-[#d5e8d4] bg-gradient-to-r from-white via-[#f8fbf8] to-[#edf7ed] px-4 py-3 shadow-[0_14px_32px_rgba(15,23,42,0.08)] xl:max-w-[420px]">
                        <div className="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#067d62] text-white shadow-sm">
                            <CheckCircle2 className="h-5 w-5" />
                        </div>
                        <div className="min-w-0">
                            <p className="text-[11px] font-black uppercase tracking-[0.22em] text-[#067d62]">Catalog updated</p>
                            <p className="mt-1 text-sm font-bold leading-5 text-[#0f1111]">{showToast}</p>
                        </div>
                    </div>
                )}
            </div>

            <div className="grid grid-cols-1 gap-8 xl:grid-cols-[1.3fr_0.85fr]">
                {/* Attribute Sets List */}
                <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 className="text-xl font-black text-gray-900 uppercase tracking-tight">All Attribute Sets</h2>
                            <p className="text-[11px] font-black text-gray-400 uppercase tracking-widest mt-0.5">
                                Product Variation Groups
                            </p>
                        </div>
                        <div className="flex w-full max-w-[340px] items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-2.5">
                            <Search className="h-4 w-4 text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search attribute sets..."
                                value={searchQuery}
                                onChange={e => setSearchQuery(e.target.value)}
                                className="w-full bg-transparent text-sm font-medium outline-none placeholder:text-gray-400"
                            />
                            {searchQuery && (
                                <button onClick={() => setSearchQuery("")} className="text-gray-400 hover:text-gray-600">
                                    <X className="h-3.5 w-3.5" />
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="divide-y divide-gray-100">
                        {!mounted ? (
                            <div className="flex items-center justify-center py-16">
                                <Loader2 className="h-6 w-6 animate-spin text-[#114f8f] opacity-30" />
                            </div>
                        ) : filtered.length === 0 ? (
                            <div className="flex flex-col items-center gap-3 py-16 text-gray-400">
                                <Layers size={36} strokeWidth={1.5} />
                                <p className="text-sm font-bold">
                                    {searchQuery ? `No sets matching "${searchQuery}"` : "No attribute sets yet. Create your first one."}
                                </p>
                            </div>
                        ) : (
                            filtered.map((set, idx) => (
                                <div key={set.id} className="transition-colors hover:bg-gray-50/30">
                                    {/* Row */}
                                    <div className="flex items-center gap-4 px-6 py-4">
                                        <span className="w-6 shrink-0 text-center text-[12px] font-black text-gray-300">{idx + 1}</span>

                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#114f8f]/5 text-[#114f8f]">
                                            <Layers size={16} />
                                        </div>

                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <span className="font-black text-gray-900">{set.name}</span>
                                                <InputTypeBadge type={set.inputType} />
                                            </div>
                                            <div className="mt-0.5 text-[11px] font-bold text-gray-400">
                                                {set.options.length} {set.options.length === 1 ? "option" : "options"}
                                            </div>
                                        </div>

                                        <div className="flex items-center gap-2 shrink-0">
                                            <Toggle enabled={set.isActive} onToggle={() => handleToggle(set.id)} />
                                        </div>

                                        <div className="flex items-center gap-2 shrink-0">
                                            <button
                                                onClick={() => setExpandedId(expandedId === set.id ? null : set.id)}
                                                className="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:border-gray-300 hover:bg-gray-50 shadow-sm"
                                                title={expandedId === set.id ? "Collapse" : "Show options"}
                                            >
                                                {expandedId === set.id
                                                    ? <ChevronUp className="h-4 w-4" />
                                                    : <ChevronDown className="h-4 w-4" />
                                                }
                                            </button>
                                            <button
                                                onClick={() => handleEdit(set)}
                                                className="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:border-[#114f8f] hover:text-[#114f8f] shadow-sm active:scale-95"
                                            >
                                                <Pencil className="h-4 w-4" />
                                            </button>
                                            <button
                                                onClick={() => handleDelete(set.id)}
                                                className="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition-all hover:border-red-500 hover:text-red-500 shadow-sm active:scale-95"
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>

                                    {/* Expanded options */}
                                    {expandedId === set.id && (
                                        <div className="border-t border-gray-100 bg-gray-50/50 px-6 py-4">
                                            <p className="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Options</p>
                                            <div className="flex flex-wrap gap-2">
                                                {set.options.map(opt => (
                                                    <div key={opt.id} className="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-1.5 shadow-sm">
                                                        {set.inputType === "color" && opt.colorHex && (
                                                            <span
                                                                className="h-4 w-4 rounded-full border border-gray-200 shrink-0"
                                                                style={{ backgroundColor: opt.colorHex }}
                                                            />
                                                        )}
                                                        <span className="text-[13px] font-bold text-gray-700">{opt.value}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ))
                        )}
                    </div>
                </section>

                {/* Add / Edit Form */}
                <section className="self-start rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-gray-200 px-6 py-5">
                        <h2 className="text-xl font-black text-gray-900 uppercase tracking-tight">
                            {editingId ? "Edit Attribute Set" : "New Attribute Set"}
                        </h2>
                        <p className="text-[11px] font-black text-gray-400 uppercase tracking-widest mt-0.5">
                            {editingId ? "Modify existing set" : "Define a variation group"}
                        </p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6 px-6 py-8">
                        {/* Name */}
                        <div>
                            <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">
                                Set Name <span className="text-red-500">*</span>
                            </label>
                            <input
                                value={formData.name}
                                onChange={e => setFormData(p => ({ ...p, name: e.target.value }))}
                                placeholder="e.g. Color, Size, Storage Capacity"
                                className="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"
                            />
                        </div>

                        {/* Input type */}
                        <div>
                            <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">
                                Input Type
                            </label>
                            <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                {(Object.keys(INPUT_TYPE_LABELS) as AttributeSet["inputType"][]).map(type => (
                                    <button
                                        key={type}
                                        type="button"
                                        onClick={() => setFormData(p => ({ ...p, inputType: type }))}
                                        className={[
                                            "rounded-xl border px-3 py-2.5 text-[12px] font-black uppercase tracking-widest transition-all",
                                            formData.inputType === type
                                                ? "border-[#114f8f] bg-[#114f8f] text-white shadow-lg shadow-blue-900/10"
                                                : "border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:bg-gray-50",
                                        ].join(" ")}
                                    >
                                        {INPUT_TYPE_LABELS[type]}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Options */}
                        <div>
                            <div className="mb-3 flex items-center justify-between">
                                <label className="text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">
                                    Options <span className="text-red-500">*</span>
                                </label>
                                <button
                                    type="button"
                                    onClick={addOption}
                                    className="inline-flex h-7 items-center gap-1.5 rounded-lg bg-[#114f8f]/5 px-2.5 text-[11px] font-black uppercase tracking-widest text-[#114f8f] transition hover:bg-[#114f8f]/10"
                                >
                                    <Plus size={12} />
                                    Add Option
                                </button>
                            </div>

                            <div className="space-y-2 max-h-[320px] overflow-y-auto pr-1">
                                {formData.options.map((opt, i) => (
                                    <div key={opt.id} className="flex items-center gap-2">
                                        {formData.inputType === "color" && (
                                            <input
                                                type="color"
                                                value={opt.colorHex || "#000000"}
                                                onChange={e => updateOption(opt.id, "colorHex", e.target.value)}
                                                className="h-9 w-9 shrink-0 cursor-pointer rounded-lg border border-gray-300 p-0.5"
                                                title="Pick color"
                                            />
                                        )}
                                        <div className="flex h-9 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-[11px] font-black text-gray-400">
                                            {i + 1}
                                        </div>
                                        <input
                                            value={opt.value}
                                            onChange={e => updateOption(opt.id, "value", e.target.value)}
                                            placeholder={`Option ${i + 1}`}
                                            className="h-9 flex-1 rounded-xl border border-gray-300 px-3 text-sm font-bold text-[#111827] outline-none transition-all focus:border-[#114f8f] focus:ring-2 focus:ring-blue-50"
                                        />
                                        {formData.options.length > 1 && (
                                            <button
                                                type="button"
                                                onClick={() => removeOption(opt.id)}
                                                className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition hover:border-red-400 hover:text-red-500"
                                            >
                                                <X size={14} />
                                            </button>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Active toggle */}
                        <div className="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3">
                            <div>
                                <p className="text-[13px] font-black uppercase tracking-widest text-gray-600">Active</p>
                                <p className="text-[11px] font-medium text-gray-400 mt-0.5">Show this set in product forms</p>
                            </div>
                            <Toggle
                                enabled={formData.isActive}
                                onToggle={() => setFormData(p => ({ ...p, isActive: !p.isActive }))}
                            />
                        </div>

                        <div className="flex gap-3 pt-2 border-t border-gray-100">
                            {editingId && (
                                <button
                                    type="button"
                                    onClick={resetForm}
                                    className="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white text-[13px] font-black uppercase tracking-wide text-gray-600 transition-all hover:bg-gray-50"
                                >
                                    <X size={16} />
                                    Cancel
                                </button>
                            )}
                            <button
                                type="submit"
                                disabled={isSaving}
                                className="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-[#114f8f] text-[13px] font-black uppercase tracking-wide text-white transition-all hover:bg-[#0d3f74] disabled:opacity-50 shadow-lg shadow-blue-900/10"
                            >
                                {isSaving ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save size={16} />}
                                {isSaving ? "Saving..." : editingId ? "Update Set" : "Create Set"}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    );
}
