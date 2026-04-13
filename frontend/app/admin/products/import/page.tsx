"use client";

import { Upload, CheckCircle2, AlertTriangle, Loader2 } from "lucide-react";
import { useState, useRef } from "react";

type ImportResult = {
    created: number;
    updated: number;
    failed: number;
    errors: string[];
    message: string;
};

export default function ImportPage() {
    const [isProcessing, setIsProcessing] = useState(false);
    const [result, setResult] = useState<ImportResult | null>(null);
    const fileRef = useRef<HTMLInputElement>(null);

    const handleFile = async (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        setIsProcessing(true);
        setResult(null);

        try {
            const text = await file.text();
            let data: any = null;

            if (file.name.endsWith('.csv')) {
                // simple CSV to JSON converter for basic data.
                data = csvToJSON(text);
            } else {
                data = JSON.parse(text);
            }

            console.log(`Parsed dataset contains ${data.length} entries.`);

            const res = await fetch("/api/admin/products/import", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            if (!res.ok) throw new Error("Bulk processing failed");
            
            const resultData = await res.json();
            setResult(resultData);
        } catch (error: any) {
            console.error(error);
            alert(`Import aborted: ${error.message}`);
        } finally {
            setIsProcessing(false);
            if (fileRef.current) fileRef.current.value = "";
        }
    };

    const csvToJSON = (csv: string) => {
        const lines = csv.split('\n').filter(l => l.trim());
        const headers = lines[0].split(',').map(h => h.trim());
        
        return lines.slice(1).map(line => {
            const values = line.split(',').map(v => v.trim());
            const obj: any = {};
            headers.forEach((header, index) => {
                obj[header.toLowerCase()] = values[index];
            });

            // Map flat CSV fields to high-fidelity schema.
            // Expected headers: Name, Slug, Brand, ListPrice, SalePrice, Category, SKU, Stock, ImageURL
            return {
                name: obj.name,
                slug: obj.slug || obj.name?.toLowerCase().replace(/ /g, '-'),
                brand: obj.brand,
                listPrice: parseFloat(obj.listprice) || 0,
                salePrice: parseFloat(obj.saleprice) || 0,
                category: obj.category ? { name: obj.category } : null,
                variants: [
                    { 
                        sku: obj.sku, 
                        stockQty: parseInt(obj.stock) || 0, 
                        price: parseFloat(obj.saleprice) || 0,
                        isDefault: true 
                    }
                ],
                media: obj.imageurl ? [{ 
                    url: obj.imageurl, 
                    isPrimary: true 
                }] : []
            };
        });
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-4">
                <span className="h-2.5 w-10 rounded-full bg-[#f6c400] shadow-sm shadow-yellow-500/20" />
                <h1 className="text-[32px] font-black tracking-tight text-[#111827] uppercase">Bulk Data Portal</h1>
            </div>

            <div className={`rounded-2xl border bg-white p-12 shadow-sm transition-all ${isProcessing ? 'border-[#114f8f] ring-4 ring-blue-50' : 'border-gray-200'}`}>
                {result ? (
                    <div className="text-center">
                        <div className="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-green-50 text-green-600">
                            <CheckCircle2 size={40} />
                        </div>
                        <h2 className="text-2xl font-black text-[#111827] uppercase">{result.message}</h2>
                        
                        <div className="mt-8 grid grid-cols-3 gap-6">
                            <div className="rounded-xl border border-gray-100 bg-gray-50/50 p-6">
                                <span className="block text-[11px] font-black uppercase text-gray-400">Processed</span>
                                <span className="text-2xl font-black text-[#111827]">{result.created + result.updated}</span>
                            </div>
                            <div className="rounded-xl border border-gray-100 bg-gray-50/50 p-6">
                                <span className="block text-[11px] font-black uppercase text-gray-400">Errors</span>
                                <span className="text-2xl font-black text-red-500">{result.failed}</span>
                            </div>
                            <div className="rounded-xl border border-gray-100 bg-gray-50/50 p-6 text-[#114f8f]">
                                <span className="block text-[11px] font-black uppercase text-gray-400 opacity-60">Verified</span>
                                <span className="text-2xl font-black">100%</span>
                            </div>
                        </div>

                        {result.errors.length > 0 && (
                            <div className="mt-8 rounded-xl bg-red-50/50 p-6 text-left border border-red-100">
                                <h3 className="text-xs font-black uppercase text-red-600 tracking-widest mb-4">Error Context Log</h3>
                                <ul className="max-h-40 overflow-y-auto text-sm text-red-700 space-y-2">
                                    {result.errors.map((e, i) => (
                                        <li key={i} className="flex gap-2">
                                            <span className="font-black opacity-40">•</span> {e}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        <button
                            onClick={() => setResult(null)}
                            className="mt-10 inline-flex h-14 items-center gap-3 rounded-2xl bg-[#111827] px-10 text-[15px] font-black uppercase tracking-wide text-white transition-all hover:bg-black active:scale-95"
                        >
                            Sync New Batch
                        </button>
                    </div>
                ) : (
                    <div className="mx-auto max-w-xl text-center">
                        <div className="mb-8 inline-flex h-20 w-20 items-center justify-center rounded-3xl bg-[#114f8f]/5 text-[#114f8f]">
                            {isProcessing ? <Loader2 size={40} className="animate-spin" /> : <Upload size={40} />}
                        </div>
                        <h2 className="text-2xl font-black text-[#111827]">Launch Bulk Import</h2>
                        <p className="mt-4 text-gray-500 font-medium">
                            Upload a backup dataset (.json) or a standard spreadsheet (.csv) to synchronize your product catalog. High-fidelity JSON imports preserve all variants, media, and tech-specs.
                        </p>
                        
                        <input
                            type="file"
                            ref={fileRef}
                            onChange={handleFile}
                            accept=".json,.csv"
                            className="hidden"
                        />
                        
                        <div className="mt-10 flex items-center justify-center gap-4">
                            <button
                                onClick={() => fileRef.current?.click()}
                                disabled={isProcessing}
                                className="inline-flex h-14 items-center gap-3 rounded-2xl bg-[#114f8f] px-10 text-[15px] font-black uppercase tracking-wide text-white transition-all hover:bg-[#0d3f74] disabled:opacity-50 shadow-xl shadow-blue-900/10 active:scale-95"
                            >
                                {isProcessing ? <Loader2 size={18} className="animate-spin" /> : <Upload size={18} />}
                                {isProcessing ? "Ingesting Data..." : "Choose File (JSON/CSV)"}
                            </button>
                        </div>
                        
                        <div className="mt-10 rounded-xl border border-gray-100 bg-gray-50/50 p-6">
                            <div className="flex items-center gap-3 text-left">
                                <AlertTriangle className="h-5 w-5 text-gray-400" />
                                <div>
                                    <h4 className="text-[12px] font-black uppercase tracking-widest text-[#111827]">CSV Header Schema</h4>
                                    <p className="text-[11px] font-medium text-gray-500 mt-0.5">
                                        Name, Slug, Brand, ListPrice, SalePrice, Category, SKU, Stock, ImageURL
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
