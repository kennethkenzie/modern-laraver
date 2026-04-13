"use client";

import { Download, Loader2 } from "lucide-react";
import { useState } from "react";

export default function ExportPage() {
    const [isExporting, setIsExporting] = useState(false);

    const handleExport = async () => {
        setIsExporting(true);
        try {
            const res = await fetch("/api/admin/products/export");
            if (!res.ok) throw new Error("Export failed");
            
            const data = await res.json();
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: "application/json" });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement("a");
            a.href = url;
            a.download = `products-export-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        } catch (error) {
            console.error(error);
            alert("Failed to export products. Please try again.");
        } finally {
            setIsExporting(false);
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-4">
                <span className="h-2.5 w-10 rounded-full bg-[#f6c400] shadow-sm shadow-yellow-500/20" />
                <h1 className="text-[32px] font-black tracking-tight text-[#111827] uppercase">Bulk Data Export</h1>
            </div>

            <div className="rounded-2xl border border-gray-200 bg-white p-12 shadow-sm">
                <div className="mx-auto max-w-xl text-center">
                    <div className="mb-8 inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-blue-50 text-[#114f8f]">
                        <Download size={40} />
                    </div>
                    <h2 className="text-2xl font-black text-[#111827]">Download Catalog Backup</h2>
                    <p className="mt-4 text-gray-500 font-medium">
                        This tool generates a complete high-fidelity JSON dump of your entire product catalog, including variants, categories, media links, and technical specifications.
                    </p>
                    
                    <button
                        onClick={handleExport}
                        disabled={isExporting}
                        className="mt-10 inline-flex h-14 items-center gap-3 rounded-2xl bg-[#111827] px-10 text-[15px] font-black uppercase tracking-wide text-white transition-all hover:bg-black disabled:opacity-50 active:scale-95"
                    >
                        {isExporting ? <Loader2 size={18} className="animate-spin" /> : <Download size={18} />}
                        {isExporting ? "Compiling Dataset..." : "Generate Export File"}
                    </button>
                    
                    <div className="mt-8 flex items-center justify-center gap-6 text-[11px] font-black uppercase tracking-widest text-gray-400">
                        <span className="flex items-center gap-2">
                            <span className="h-1.5 w-1.5 rounded-full bg-green-500" />
                            Full Metadata
                        </span>
                        <span className="flex items-center gap-2">
                            <span className="h-1.5 w-1.5 rounded-full bg-green-500" />
                            Media URLs
                        </span>
                        <span className="flex items-center gap-2">
                            <span className="h-1.5 w-1.5 rounded-full bg-green-500" />
                            Inventory
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}
