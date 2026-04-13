"use client";

import {
    Search,
    Pencil,
    Trash2,
    Loader2,
    Save,
    CheckCircle2,
    X,
    Ruler,
} from "lucide-react";
import { useState, useEffect } from "react";

type Unit = {
    id: string;
    name: string;
    shortName: string;
    isActive: boolean;
    createdAt: string;
};

const STORAGE_KEY = "admin_units_v1";

const DEFAULT_UNITS: Unit[] = [
    { id: "unit-1", name: "Piece",     shortName: "pcs",  isActive: true,  createdAt: "2026-01-01" },
    { id: "unit-2", name: "Kilogram",  shortName: "kg",   isActive: true,  createdAt: "2026-01-01" },
    { id: "unit-3", name: "Gram",      shortName: "g",    isActive: true,  createdAt: "2026-01-01" },
    { id: "unit-4", name: "Litre",     shortName: "L",    isActive: true,  createdAt: "2026-01-01" },
    { id: "unit-5", name: "Metre",     shortName: "m",    isActive: true,  createdAt: "2026-01-01" },
    { id: "unit-6", name: "Box",       shortName: "box",  isActive: true,  createdAt: "2026-01-01" },
    { id: "unit-7", name: "Pair",      shortName: "pr",   isActive: false, createdAt: "2026-01-01" },
    { id: "unit-8", name: "Set",       shortName: "set",  isActive: true,  createdAt: "2026-01-01" },
];

function loadUnits(): Unit[] {
    if (typeof window === "undefined") return DEFAULT_UNITS;
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : DEFAULT_UNITS;
    } catch {
        return DEFAULT_UNITS;
    }
}

function saveUnits(units: Unit[]) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(units));
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

export default function UnitsPage() {
    const [units, setUnits] = useState<Unit[]>([]);
    const [mounted, setMounted] = useState(false);
    const [isSaving, setIsSaving] = useState(false);
    const [showToast, setShowToast] = useState<string | null>(null);
    const [searchQuery, setSearchQuery] = useState("");
    const [editingId, setEditingId] = useState<string | null>(null);

    const [formData, setFormData] = useState({ name: "", shortName: "", isActive: true });

    useEffect(() => {
        setUnits(loadUnits());
        setMounted(true);
    }, []);

    useEffect(() => {
        if (!showToast) return;
        const t = window.setTimeout(() => setShowToast(null), 3200);
        return () => window.clearTimeout(t);
    }, [showToast]);

    const resetForm = () => {
        setFormData({ name: "", shortName: "", isActive: true });
        setEditingId(null);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!formData.name.trim()) return alert("Unit name is required");
        if (!formData.shortName.trim()) return alert("Short name is required");

        setIsSaving(true);
        await new Promise(r => setTimeout(r, 300));

        const next: Unit = {
            id: editingId || crypto.randomUUID(),
            name: formData.name.trim(),
            shortName: formData.shortName.trim(),
            isActive: formData.isActive,
            createdAt: editingId
                ? units.find(u => u.id === editingId)?.createdAt ?? new Date().toISOString().split("T")[0]
                : new Date().toISOString().split("T")[0],
        };

        const updated = editingId
            ? units.map(u => u.id === editingId ? next : u)
            : [next, ...units];

        setUnits(updated);
        saveUnits(updated);
        setShowToast(editingId ? "Unit updated successfully." : "Unit added successfully.");
        resetForm();
        setIsSaving(false);
    };

    const handleEdit = (unit: Unit) => {
        setEditingId(unit.id);
        setFormData({ name: unit.name, shortName: unit.shortName, isActive: unit.isActive });
        window.scrollTo({ top: 0, behavior: "smooth" });
    };

    const handleDelete = (id: string) => {
        if (!confirm("Delete this unit?")) return;
        const updated = units.filter(u => u.id !== id);
        setUnits(updated);
        saveUnits(updated);
        if (editingId === id) resetForm();
    };

    const handleToggle = (id: string) => {
        const updated = units.map(u => u.id === id ? { ...u, isActive: !u.isActive } : u);
        setUnits(updated);
        saveUnits(updated);
    };

    const filtered = units.filter(u =>
        u.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        u.shortName.toLowerCase().includes(searchQuery.toLowerCase())
    );

    return (
        <div className="space-y-8">
            {/* Header */}
            <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div className="flex items-start gap-4">
                    <span className="mt-2 h-2.5 w-10 rounded-full bg-[#f6c400] shadow-sm shadow-yellow-500/20" />
                    <div>
                        <h1 className="text-[32px] font-black tracking-tight text-gray-900 uppercase leading-none">
                            Units
                        </h1>
                        <p className="mt-2 text-sm font-bold text-gray-400 uppercase tracking-[0.15em]">
                            {mounted ? units.length : "..."} {units.length === 1 ? "unit" : "units"} configured
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

            <div className="grid grid-cols-1 gap-8 xl:grid-cols-[1.4fr_0.7fr]">
                {/* Units List */}
                <section className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div className="flex flex-col gap-4 border-b border-gray-200 px-6 py-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 className="text-xl font-black text-gray-900 uppercase tracking-tight">All Units</h2>
                            <p className="text-[11px] font-black text-gray-400 uppercase tracking-widest mt-0.5">
                                Units of Measurement
                            </p>
                        </div>
                        <div className="flex w-full max-w-[340px] items-center gap-3 rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-2.5">
                            <Search className="h-4 w-4 text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search units..."
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

                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead>
                                <tr className="border-b border-gray-100 bg-gray-50/50 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">
                                    <th className="px-6 py-4">#</th>
                                    <th className="px-6 py-4">Unit Name</th>
                                    <th className="px-6 py-4">Short Name</th>
                                    <th className="px-6 py-4">Created</th>
                                    <th className="px-6 py-4">Status</th>
                                    <th className="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {!mounted ? (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-12 text-center">
                                            <Loader2 className="mx-auto h-6 w-6 animate-spin text-[#114f8f] opacity-30" />
                                        </td>
                                    </tr>
                                ) : filtered.length === 0 ? (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-12 text-center">
                                            <div className="flex flex-col items-center gap-3 text-gray-400">
                                                <Ruler size={32} strokeWidth={1.5} />
                                                <p className="text-sm font-bold">
                                                    {searchQuery ? `No units matching "${searchQuery}"` : "No units yet. Add your first unit."}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                ) : (
                                    filtered.map((unit, idx) => (
                                        <tr key={unit.id} className="group transition-colors hover:bg-gray-50/40">
                                            <td className="px-6 py-4 font-bold text-gray-400">{idx + 1}</td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-3">
                                                    <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#114f8f]/5 text-[#114f8f]">
                                                        <Ruler size={15} />
                                                    </div>
                                                    <span className="font-bold text-gray-900">{unit.name}</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className="inline-flex h-7 items-center rounded-lg bg-gray-100 px-3 text-[13px] font-black uppercase tracking-widest text-gray-600">
                                                    {unit.shortName}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 font-medium text-gray-500">{unit.createdAt}</td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-2">
                                                    <Toggle enabled={unit.isActive} onToggle={() => handleToggle(unit.id)} />
                                                    <span className={`text-[11px] font-black uppercase tracking-widest ${unit.isActive ? "text-[#114f8f]" : "text-gray-400"}`}>
                                                        {unit.isActive ? "Active" : "Inactive"}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <div className="flex justify-end gap-2">
                                                    <button
                                                        onClick={() => handleEdit(unit)}
                                                        className="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:border-[#114f8f] hover:text-[#114f8f] active:scale-95 shadow-sm"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(unit.id)}
                                                        className="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition-all hover:border-red-500 hover:text-red-500 active:scale-95 shadow-sm"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                {/* Add / Edit Form */}
                <section className="self-start rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-gray-200 px-6 py-5">
                        <h2 className="text-xl font-black text-gray-900 uppercase tracking-tight">
                            {editingId ? "Edit Unit" : "Add Unit"}
                        </h2>
                        <p className="text-[11px] font-black text-gray-400 uppercase tracking-widest mt-0.5">
                            {editingId ? "Modify existing unit" : "Create new unit"}
                        </p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6 px-6 py-8">
                        <div>
                            <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">
                                Unit Name <span className="text-red-500">*</span>
                            </label>
                            <input
                                value={formData.name}
                                onChange={e => setFormData(p => ({ ...p, name: e.target.value }))}
                                placeholder="e.g. Kilogram"
                                className="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"
                            />
                        </div>

                        <div>
                            <label className="mb-2 block text-[13px] font-black uppercase tracking-widest text-gray-500 leading-none">
                                Short Name <span className="text-red-500">*</span>
                            </label>
                            <input
                                value={formData.shortName}
                                onChange={e => setFormData(p => ({ ...p, shortName: e.target.value }))}
                                placeholder="e.g. kg"
                                className="h-11 w-full rounded-xl border border-gray-300 px-4 text-sm font-bold text-[#111827] outline-none transition-all hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50"
                            />
                            <p className="mt-1.5 text-[11px] font-medium text-gray-400">The abbreviated symbol shown on product pages.</p>
                        </div>

                        <div className="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3">
                            <div>
                                <p className="text-[13px] font-black uppercase tracking-widest text-gray-600">Active</p>
                                <p className="text-[11px] font-medium text-gray-400 mt-0.5">Show this unit in product forms</p>
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
                                {isSaving ? "Saving..." : editingId ? "Update Unit" : "Save Unit"}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    );
}
