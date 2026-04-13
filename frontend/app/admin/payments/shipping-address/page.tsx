"use client";

import { useMemo, useState } from "react";
import { Building2, ChevronDown, Loader2, MapPin, Phone, Plus, Save, Trash2 } from "lucide-react";
import { useFrontendData } from "@/lib/use-frontend-data";
import { writeFrontendData } from "@/lib/frontend-data-store";
import type { PickupLocation } from "@/lib/frontend-data";

const locationOptions = {
  Uganda: {
    "Central Region": ["Kampala", "Entebbe", "Mukono", "Wakiso", "Masaka"],
    "Eastern Region": ["Jinja", "Mbale", "Soroti", "Tororo", "Iganga"],
    "Northern Region": ["Gulu", "Lira", "Arua", "Kitgum", "Pader"],
    "Western Region": ["Mbarara", "Fort Portal", "Kasese", "Kabale", "Hoima"],
  },
  Kenya: {
    Nairobi: ["Westlands", "Karen", "Kilimani", "Embakasi"],
    Mombasa: ["Nyali", "Likoni", "Bamburi", "Changamwe"],
  },
  Tanzania: {
    DarEsSalaam: ["Ilala", "Kinondoni", "Temeke"],
    Arusha: ["Arusha City", "Njiro", "Meru"],
  },
  Rwanda: {
    Kigali: ["Gasabo", "Kicukiro", "Nyarugenge"],
  },
  "South Sudan": {
    Juba: ["Juba Central", "Kator", "Munuki"],
  },
} as const;

const countryOptions = Object.keys(locationOptions) as Array<keyof typeof locationOptions>;

function createLocation(): PickupLocation {
  return {
    id: `pickup-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    title: "New Pickup Location",
    contactName: "",
    phone: "",
    email: "",
    addressLine1: "",
    addressLine2: "",
    country: "Uganda",
    state: "Central Region",
    city: "Kampala",
    postalCode: "",
    isActive: true,
  };
}

export default function PickupLocationsPage() {
  const { data, isLoading } = useFrontendData();
  const [selectedId, setSelectedId] = useState<string | null>(null);
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);

  const locations = data?.pickupLocations ?? [];
  const selectedLocation =
    locations.find((location) => location.id === selectedId) ?? locations[0] ?? null;

  const activeStates = useMemo(() => {
    if (!selectedLocation) return [];
    return Object.keys(locationOptions[selectedLocation.country as keyof typeof locationOptions] || {});
  }, [selectedLocation]);

  const activeCities = useMemo(() => {
    if (!selectedLocation) return [];
    const states =
      locationOptions[selectedLocation.country as keyof typeof locationOptions] || {};
    return states[selectedLocation.state as keyof typeof states] || [];
  }, [selectedLocation]);

  if (isLoading) {
    return (
      <div className="flex h-[400px] flex-col items-center justify-center rounded-[32px] bg-white border-2 border-dashed border-gray-100 shadow-sm transition-all animate-pulse">
        <Loader2 className="h-10 w-10 animate-spin text-[#114f8f] opacity-20" />
        <p className="mt-4 text-[11px] font-black uppercase tracking-widest text-gray-400">Loading Locations...</p>
      </div>
    );
  }

  async function persist(nextLocations: PickupLocation[]) {
    setSaving(true);
    setSaved(false);
    await writeFrontendData({
      ...data,
      pickupLocations: nextLocations,
    });
    setSaving(false);
    setSaved(true);
    window.setTimeout(() => setSaved(false), 1800);
  }

  async function addLocation() {
    const next = createLocation();
    const nextLocations = [...locations, next];
    setSelectedId(next.id);
    await persist(nextLocations);
  }

  async function removeLocation(id: string) {
    const nextLocations = locations.filter((location) => location.id !== id);
    setSelectedId(nextLocations[0]?.id || null);
    await persist(nextLocations);
  }

  function updateLocation(patch: Partial<PickupLocation>) {
    if (!selectedLocation) return;

    const nextLocations = locations.map((location) =>
      location.id === selectedLocation.id ? { ...location, ...patch } : location
    );

    void persist(nextLocations);
  }

  return (
    <div className="space-y-6">
      <section className="rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm">
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#0b63ce]">
              Pickup Locations
            </p>
            <h1 className="mt-2 text-3xl font-bold tracking-tight text-gray-900">
              Manage pickup and dispatch points
            </h1>
            <p className="mt-3 max-w-3xl text-sm leading-6 text-gray-500">
              Register multiple pickup branches, warehouse counters, or dispatch hubs and keep them available for your shipping setup.
            </p>
          </div>

          <button
            type="button"
            onClick={() => void addLocation()}
            className="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#111827] px-5 text-sm font-bold text-white transition hover:bg-black"
          >
            <Plus size={16} />
            Add location
          </button>
        </div>
      </section>

      <div className="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)_360px]">
        <aside className="rounded-3xl border border-[#e5e7eb] bg-white p-5 shadow-sm">
          <h2 className="text-lg font-bold text-gray-900">Registered locations</h2>
          <div className="mt-4 space-y-3">
            {locations.length === 0 ? (
              <div className="rounded-2xl border border-dashed border-[#d1d5db] bg-[#f9fafb] px-4 py-6 text-sm text-gray-500">
                No pickup locations yet. Add your first location.
              </div>
            ) : (
              locations.map((location) => (
                <button
                  key={location.id}
                  type="button"
                  onClick={() => setSelectedId(location.id)}
                  className={`w-full rounded-2xl border px-4 py-4 text-left transition ${
                    selectedLocation?.id === location.id
                      ? "border-[#114f8f] bg-[#eef4ff]"
                      : "border-[#e5e7eb] bg-white hover:bg-[#f9fafb]"
                  }`}
                >
                  <div className="flex items-start justify-between gap-3">
                    <div>
                      <div className="text-sm font-bold text-gray-900">{location.title}</div>
                      <div className="mt-1 text-xs text-gray-500">
                        {location.city}, {location.state}
                      </div>
                    </div>
                    <span
                      className={`rounded-full px-2.5 py-1 text-[11px] font-bold ${
                        location.isActive
                          ? "bg-[#ecfdf3] text-[#16a34a]"
                          : "bg-[#f3f4f6] text-[#6b7280]"
                      }`}
                    >
                      {location.isActive ? "Active" : "Inactive"}
                    </span>
                  </div>
                </button>
              ))
            )}
          </div>
        </aside>

        <section className="rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm">
          {selectedLocation ? (
            <>
              <div className="flex items-center justify-between gap-4">
                <h2 className="text-xl font-bold text-gray-900">Location details</h2>
                <button
                  type="button"
                  onClick={() => void removeLocation(selectedLocation.id)}
                  className="inline-flex items-center gap-2 rounded-xl border border-[#fecaca] bg-white px-4 py-2 text-sm font-bold text-[#dc2626] hover:bg-[#fef2f2]"
                >
                  <Trash2 size={15} />
                  Remove
                </button>
              </div>

              <div className="mt-6 grid gap-5 md:grid-cols-2">
                <Field
                  label="Location name"
                  value={selectedLocation.title}
                  onChange={(value) => updateLocation({ title: value })}
                  icon={<Building2 size={18} className="text-gray-400" />}
                />
                <Field
                  label="Contact person"
                  value={selectedLocation.contactName}
                  onChange={(value) => updateLocation({ contactName: value })}
                  icon={<MapPin size={18} className="text-gray-400" />}
                />
                <Field
                  label="Phone number"
                  value={selectedLocation.phone}
                  onChange={(value) => updateLocation({ phone: value })}
                  icon={<Phone size={18} className="text-gray-400" />}
                />
                <Field
                  label="Support email"
                  value={selectedLocation.email}
                  onChange={(value) => updateLocation({ email: value })}
                />
                <div className="md:col-span-2">
                  <Field
                    label="Address line 1"
                    value={selectedLocation.addressLine1}
                    onChange={(value) => updateLocation({ addressLine1: value })}
                  />
                </div>
                <div className="md:col-span-2">
                  <Field
                    label="Address line 2"
                    value={selectedLocation.addressLine2 || ""}
                    onChange={(value) => updateLocation({ addressLine2: value })}
                  />
                </div>
                <SelectField
                  label="Country"
                  value={selectedLocation.country}
                  onChange={(value) => {
                    const nextCountry = value as keyof typeof locationOptions;
                    const nextStates = Object.keys(locationOptions[nextCountry]);
                    const nextState = nextStates[0] || "";
                    const nextCities =
                      locationOptions[nextCountry][
                        nextState as keyof (typeof locationOptions)[typeof nextCountry]
                      ] || [];

                    updateLocation({
                      country: nextCountry,
                      state: nextState,
                      city: nextCities[0] || "",
                    });
                  }}
                  options={countryOptions}
                />
                <SelectField
                  label="State / Region"
                  value={selectedLocation.state}
                  onChange={(value) => {
                    const stateMap =
                      locationOptions[selectedLocation.country as keyof typeof locationOptions];
                    const nextCities =
                      stateMap[value as keyof typeof stateMap] || [];
                    updateLocation({
                      state: value,
                      city: nextCities[0] || "",
                    });
                  }}
                  options={activeStates}
                />
                <SelectField
                  label="City"
                  value={selectedLocation.city}
                  onChange={(value) => updateLocation({ city: value })}
                  options={activeCities}
                />
                <Field
                  label="Postal / ZIP code"
                  value={selectedLocation.postalCode || ""}
                  onChange={(value) => updateLocation({ postalCode: value })}
                />
              </div>

              <div className="mt-6 flex flex-wrap items-center gap-3">
                <button
                  type="button"
                  onClick={() =>
                    updateLocation({ isActive: !selectedLocation.isActive })
                  }
                  className="inline-flex h-11 items-center justify-center rounded-xl border border-[#d1d5db] bg-white px-5 text-sm font-bold text-[#111827] hover:bg-[#f9fafb]"
                >
                  Mark as {selectedLocation.isActive ? "inactive" : "active"}
                </button>
                <div className="inline-flex h-11 items-center gap-2 rounded-xl bg-[#111827] px-5 text-sm font-bold text-white">
                  <Save size={16} />
                  {saving ? "Saving..." : saved ? "Saved" : "Autosaved"}
                </div>
              </div>
            </>
          ) : (
            <div className="rounded-2xl border border-dashed border-[#d1d5db] bg-[#f9fafb] px-4 py-10 text-center text-sm text-gray-500">
              Select a pickup location or create a new one.
            </div>
          )}
        </section>

        <aside className="space-y-6">
          <section className="rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm">
            <h2 className="text-lg font-bold text-gray-900">Preview</h2>
            {selectedLocation ? (
              <div className="mt-4 rounded-2xl border border-dashed border-[#d1d5db] bg-[#f9fafb] p-4 text-sm leading-6 text-gray-700">
                <div className="font-bold text-gray-900">{selectedLocation.title}</div>
                <div>{selectedLocation.contactName}</div>
                <div>{selectedLocation.addressLine1}</div>
                {selectedLocation.addressLine2 ? <div>{selectedLocation.addressLine2}</div> : null}
                <div>
                  {selectedLocation.city}, {selectedLocation.state}
                </div>
                <div>
                  {selectedLocation.country} {selectedLocation.postalCode}
                </div>
                <div className="mt-3">{selectedLocation.phone}</div>
                <div>{selectedLocation.email}</div>
              </div>
            ) : null}
          </section>

          <section className="rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm">
            <h2 className="text-lg font-bold text-gray-900">Usage</h2>
            <ul className="mt-4 list-disc space-y-2 pl-5 text-sm text-gray-600">
              <li>Register multiple store branches and warehouse pickup points</li>
              <li>Use active locations for customer collection and dispatch handover</li>
              <li>Keep contact details different for each pickup location</li>
            </ul>
          </section>
        </aside>
      </div>
    </div>
  );
}

function Field({
  label,
  value,
  onChange,
  icon,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  icon?: React.ReactNode;
}) {
  return (
    <label className="block">
      <span className="mb-2 block text-sm font-semibold text-[#374151]">{label}</span>
      <div className="flex items-center gap-3 rounded-xl border border-[#d1d5db] px-4 py-3 focus-within:border-[#114f8f]">
        {icon}
        <input
          value={value}
          onChange={(event) => onChange(event.target.value)}
          className="w-full bg-transparent text-sm text-[#111827] outline-none"
        />
      </div>
    </label>
  );
}

function SelectField({
  label,
  value,
  onChange,
  options,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  options: readonly string[];
}) {
  return (
    <label className="block">
      <span className="mb-2 block text-sm font-semibold text-[#374151]">{label}</span>
      <div className="relative rounded-xl border border-[#d1d5db] bg-white px-4 py-3 transition-all focus-within:border-[#114f8f] focus-within:ring-4 focus-within:ring-blue-50/50">
        <select
          value={value}
          onChange={(event) => onChange(event.target.value)}
          className="w-full cursor-pointer appearance-none bg-transparent pr-8 text-sm font-bold text-[#111827] outline-none"
        >
          {options.map((option) => (
            <option key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
        <ChevronDown
          size={16}
          className="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"
        />
      </div>
    </label>
  );
}
