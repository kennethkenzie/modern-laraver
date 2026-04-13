"use client";

import { useEffect, useRef, useState } from "react";
import {
  CheckCircle2,
  Loader2,
  Lock,
  RefreshCcw,
  Save,
  ToggleLeft,
  ToggleRight,
  Trash2,
  Upload,
  X,
} from "lucide-react";
import { useFrontendData } from "@/lib/use-frontend-data";
import { fetchFrontendData, writeFrontendData } from "@/lib/frontend-data-store";
import type { FrontendData, PaymentGateway } from "@/lib/frontend-data";

const AUTO_SAVE_DELAY_MS = 1200;

export default function GatewaysPage() {
  const { data, isLoading } = useFrontendData();
  const [gateways, setGateways] = useState<PaymentGateway[] | null>(null);
  const [isSaving, setIsSaving] = useState(false);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isUploading, setIsUploading] = useState(false);
  const [activeUploadId, setActiveUploadId] = useState<string | null>(null);
  const [saveState, setSaveState] = useState<"idle" | "dirty" | "saved" | "error">("idle");
  const fileInputRef = useRef<HTMLInputElement>(null);
  const hasHydratedRef = useRef(false);
  const pendingChangesRef = useRef(false);
  const autoSaveTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (isLoading || !data?.gateways) return;

    if (!hasHydratedRef.current || !pendingChangesRef.current) {
      setGateways(JSON.parse(JSON.stringify(data.gateways)));
      hasHydratedRef.current = true;
    }
  }, [data, isLoading]);

  useEffect(() => {
    return () => {
      if (autoSaveTimerRef.current) {
        clearTimeout(autoSaveTimerRef.current);
      }
    };
  }, []);

  useEffect(() => {
    if (!hasHydratedRef.current || !gateways || !pendingChangesRef.current) return;

    if (autoSaveTimerRef.current) {
      clearTimeout(autoSaveTimerRef.current);
    }

    setSaveState("dirty");
    autoSaveTimerRef.current = setTimeout(() => {
      void persistGateways(gateways);
    }, AUTO_SAVE_DELAY_MS);
  }, [gateways]);

  const persistGateways = async (nextGateways: PaymentGateway[]) => {
    if (!data) return;

    setIsSaving(true);
    try {
      const payload: FrontendData = {
        ...data,
        gateways: nextGateways,
      };

      await writeFrontendData(payload);
      pendingChangesRef.current = false;
      setSaveState("saved");
    } catch (error) {
      console.error("Failed to save payment gateways.", error);
      setSaveState("error");
    } finally {
      setIsSaving(false);
    }
  };

  const updateGateways = (updater: (current: PaymentGateway[]) => PaymentGateway[]) => {
    setGateways((current) => {
      if (!current) return current;
      pendingChangesRef.current = true;
      return updater(current);
    });
  };

  const toggleGateway = (id: string) => {
    updateGateways((current) =>
      current.map((gateway) =>
        gateway.id === id ? { ...gateway, enabled: !gateway.enabled } : gateway
      )
    );
  };

  const updateGateway = (id: string, patch: Partial<PaymentGateway>) => {
    updateGateways((current) =>
      current.map((gateway) =>
        gateway.id === id ? { ...gateway, ...patch } : gateway
      )
    );
  };

  const handleUploadClick = (id: string) => {
    setActiveUploadId(id);
    fileInputRef.current?.click();
  };

  const handleFileUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file || !activeUploadId) return;

    setIsUploading(true);
    try {
      const formData = new FormData();
      formData.append("file", file);

      const response = await fetch("/api/upload", {
        method: "POST",
        body: formData,
      });

      const raw = await response.text();
      let result: { url?: string; error?: string } = {};

      if (raw) {
        try {
          result = JSON.parse(raw) as { url?: string; error?: string };
        } catch {
          result = { error: raw };
        }
      }

      if (!response.ok) {
        throw new Error(result.error || "Upload failed");
      }

      if (result.url) {
        updateGateway(activeUploadId, { logo: result.url });
      }
    } catch (error) {
      console.error("Gateway logo upload error:", error);
      alert("Failed to upload gateway logo.");
    } finally {
      setIsUploading(false);
      setActiveUploadId(null);
      if (fileInputRef.current) fileInputRef.current.value = "";
    }
  };

  const handleManualSave = async () => {
    if (!gateways) return;
    if (autoSaveTimerRef.current) {
      clearTimeout(autoSaveTimerRef.current);
    }
    pendingChangesRef.current = true;
    await persistGateways(gateways);
  };

  const forceResync = async () => {
    setIsRefreshing(true);
    try {
      if (autoSaveTimerRef.current) {
        clearTimeout(autoSaveTimerRef.current);
      }

      const freshData = await fetchFrontendData();
      pendingChangesRef.current = false;
      hasHydratedRef.current = true;
      setGateways(JSON.parse(JSON.stringify(freshData.gateways ?? [])));
      setSaveState("idle");
    } finally {
      setIsRefreshing(false);
    }
  };

  if (!gateways) {
    return (
      <div className="flex h-[400px] flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white shadow-sm transition-all animate-pulse">
        <Loader2 className="h-10 w-10 animate-spin text-[#114f8f] opacity-20" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#f8fbff]">
      <div className="mb-12 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div className="flex items-start gap-4">
          <span className="mt-2 h-2.5 w-10 rounded-full bg-[#f6c400] shadow-sm shadow-yellow-500/20" />
          <div>
            <h1 className="text-[36px] font-black leading-none tracking-tighter text-[#111827] text-blue-950 uppercase">
              Gateway Terminal
            </h1>
            <p className="mt-2 text-[11px] font-black uppercase tracking-[0.25em] text-gray-400">
              Configure Transaction & Settlement Channels
            </p>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <button
            onClick={forceResync}
            className="flex items-center gap-2 rounded-xl border border-transparent px-4 py-2 text-[11px] font-black uppercase text-gray-400 transition-all hover:border-gray-100 hover:bg-white hover:text-[#114f8f]"
            disabled={isRefreshing}
          >
            <RefreshCcw size={14} className={isRefreshing ? "animate-spin" : ""} />
            Force Resync
          </button>

          <StatusPill saveState={saveState} />
        </div>
      </div>

      <input
        type="file"
        ref={fileInputRef}
        onChange={handleFileUpload}
        className="hidden"
        accept="image/*,.svg"
      />

      <div className="grid gap-8 md:grid-cols-2">
        {gateways.map((gateway) => (
          <div
            key={gateway.id}
            className="group relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:border-[#114f8f]/20 hover:shadow-xl"
          >
            <div className="flex items-start justify-between">
              <div className="relative h-20 w-32 shrink-0 overflow-hidden rounded-2xl border border-gray-100 bg-[#f8fbff] p-3 shadow-inner transition-all group-hover:bg-white">
                {gateway.logo ? (
                  <div className="relative h-full w-full">
                    <img
                      src={gateway.logo}
                      alt={gateway.name}
                      className="h-full w-full object-contain"
                    />
                    <button
                      onClick={() => updateGateway(gateway.id, { logo: "" })}
                      className="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white opacity-0 shadow-lg transition-opacity group-hover:opacity-100"
                    >
                      <X size={12} strokeWidth={3} />
                    </button>
                  </div>
                ) : (
                  <button
                    onClick={() => handleUploadClick(gateway.id)}
                    className="flex h-full w-full flex-col items-center justify-center gap-1.5 text-gray-400 transition-colors hover:text-[#114f8f]"
                  >
                    {isUploading && activeUploadId === gateway.id ? (
                      <Loader2 size={16} className="animate-spin" />
                    ) : (
                      <Upload size={18} />
                    )}
                    <span className="text-[8px] font-black uppercase tracking-widest leading-none">
                      Add Logo
                    </span>
                  </button>
                )}
              </div>

              <button
                onClick={() => toggleGateway(gateway.id)}
                className={`transition-all active:scale-95 ${
                  gateway.enabled ? "text-[#114f8f]" : "text-gray-200"
                }`}
              >
                {gateway.enabled ? (
                  <ToggleRight size={56} strokeWidth={1.5} />
                ) : (
                  <ToggleLeft size={56} strokeWidth={1.5} />
                )}
              </button>
            </div>

            <div className="mt-8">
              <div className="flex items-center justify-between gap-3">
                <div className="flex items-center gap-3">
                  <h3 className="text-[22px] font-black uppercase tracking-tighter text-[#111827]">
                    {gateway.name}
                  </h3>
                  <div
                    className={`h-1.5 w-1.5 rounded-full ${
                      gateway.enabled ? "bg-emerald-500 animate-pulse" : "bg-gray-300"
                    }`}
                  />
                </div>
                <div className="text-[10px] font-bold uppercase tracking-widest text-gray-300">
                  {gateway.id}
                </div>
              </div>
              <p className="mt-3 text-sm font-bold uppercase tracking-widest leading-relaxed text-gray-400">
                {gateway.description}
              </p>
            </div>

            <div className="mt-8 space-y-6 border-t border-gray-50 pt-8">
              <div className="grid gap-4">
                <label className="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.2em] leading-none text-gray-500">
                  <Lock size={12} className="text-[#114f8f]" />
                  Gateway Logo URL
                </label>
                <div className="flex flex-col gap-2">
                  <input
                    value={gateway.logo || ""}
                    onChange={(event) => updateGateway(gateway.id, { logo: event.target.value })}
                    placeholder="Logo URL will appear here after upload..."
                    className="h-12 w-full rounded-2xl border border-gray-100 bg-gray-50/50 px-5 text-[11px] font-medium text-[#111827]/60 outline-none focus:border-[#114f8f]/30"
                  />
                  <div className="flex gap-2">
                    <input
                      value={`${gateway.id.toLowerCase().replace(/\s+/g, "_")}_live_id`}
                      className="h-12 w-full rounded-2xl border border-gray-100 bg-gray-50/50 px-5 text-sm font-bold text-[#111827]/40 outline-none"
                      readOnly
                    />
                    <button
                      type="button"
                      onClick={() => updateGateway(gateway.id, { logo: "" })}
                      className="flex h-12 w-12 items-center justify-center rounded-2xl border border-gray-100 text-gray-400 transition-colors hover:bg-[#114f8f] hover:text-white"
                    >
                      <Trash2 size={18} />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="mt-12 flex items-center justify-end border-t border-gray-100 pt-10">
        <button
          onClick={handleManualSave}
          disabled={isSaving}
          className="inline-flex h-16 items-center justify-center gap-4 rounded-2xl bg-[#114f8f] px-12 text-[16px] font-black uppercase tracking-widest text-white shadow-2xl shadow-blue-950/20 transition-all hover:bg-[#0d3f74] active:scale-95 disabled:opacity-50"
        >
          {isSaving ? <Loader2 size={24} className="animate-spin" /> : <Save size={24} />}
          {isSaving ? "Syncing Network..." : "Save Now"}
        </button>
      </div>
    </div>
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
      className={`flex items-center gap-2 rounded-2xl border px-6 py-4 text-[13px] font-black uppercase tracking-wider shadow-sm ${config.className}`}
    >
      <CheckCircle2 size={18} />
      {config.label}
    </div>
  );
}
