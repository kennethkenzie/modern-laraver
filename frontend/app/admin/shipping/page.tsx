"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { ChevronDown, ChevronRight, Globe2, MapPin, Save, Truck } from "lucide-react";

const countryOptions = ["Uganda", "Kenya", "Tanzania", "Rwanda", "South Sudan"] as const;

const stateCityOptions = {
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

export default function ShippingConfigurationPage() {
  const [country, setCountry] = useState<(typeof countryOptions)[number]>("Uganda");
  const [region, setRegion] = useState("Central Region");
  const [city, setCity] = useState("Kampala");
  const [saved, setSaved] = useState(false);

  const availableStates = useMemo(
    () => Object.keys(stateCityOptions[country]),
    [country]
  );

  const availableCities = useMemo(() => {
    const stateMap = stateCityOptions[country];
    return stateMap[region as keyof typeof stateMap] || [];
  }, [country, region]);

  const handleSave = () => {
    setSaved(true);
    window.setTimeout(() => setSaved(false), 1800);
  };

  return (
    <div className="space-y-6">
      <section className="rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm">
        <p className="text-sm font-bold uppercase tracking-[0.18em] text-[#0b63ce]">
          Shipping
        </p>
        <h1 className="mt-2 text-3xl font-bold tracking-tight text-gray-900">
          Shipping configuration
        </h1>
        <p className="mt-3 max-w-3xl text-sm leading-6 text-gray-500">
          Configure where orders can be delivered and manage location availability for shipping.
        </p>
      </section>

      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_380px]">
        <section className="rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm">
          <h2 className="text-xl font-bold text-gray-900">Delivery availability</h2>
          <p className="mt-2 text-sm text-gray-500">
            Choose the active country, state, and city coverage for delivery setup.
          </p>

          <div className="mt-6 grid gap-5 md:grid-cols-3">
            <SelectField
              label="Available Countries"
              value={country}
              options={countryOptions}
              onChange={(value) => {
                const nextCountry = value as (typeof countryOptions)[number];
                const nextStates = Object.keys(stateCityOptions[nextCountry]);
                const nextRegion = nextStates[0] || "";
                const nextCities =
                  stateCityOptions[nextCountry][
                    nextRegion as keyof (typeof stateCityOptions)[typeof nextCountry]
                  ] || [];

                setCountry(nextCountry);
                setRegion(nextRegion);
                setCity(nextCities[0] || "");
              }}
              icon={<Globe2 size={16} className="text-gray-400" />}
            />

            <SelectField
              label="Available States"
              value={region}
              options={availableStates}
              onChange={(value) => {
                setRegion(value);
                const nextCities =
                  stateCityOptions[country][
                    value as keyof (typeof stateCityOptions)[typeof country]
                  ] || [];
                setCity(nextCities[0] || "");
              }}
              icon={<MapPin size={16} className="text-gray-400" />}
            />

            <SelectField
              label="Available Cities"
              value={city}
              options={availableCities}
              onChange={setCity}
              icon={<Truck size={16} className="text-gray-400" />}
            />
          </div>

          <div className="mt-6 flex flex-wrap items-center gap-3">
            <button
              type="button"
              onClick={handleSave}
              className="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#111827] px-5 text-sm font-bold text-white transition hover:bg-black"
            >
              <Save size={16} />
              Save shipping config
            </button>
            {saved ? (
              <span className="text-sm font-semibold text-[#16a34a]">
                Shipping configuration saved.
              </span>
            ) : null}
          </div>
        </section>

        <aside className="space-y-6">
          <Link
            href="/admin/payments/shipping-address"
            className="block rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm transition hover:border-[#cbd5e1] hover:bg-[#fcfcfd]"
          >
            <div className="flex items-start justify-between gap-4">
              <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eef4ff] text-[#114f8f]">
                <MapPin size={20} />
              </div>
              <ChevronRight size={18} className="text-gray-400" />
            </div>
            <h2 className="mt-4 text-xl font-bold text-gray-900">Pickup Locations</h2>
            <p className="mt-2 text-sm leading-6 text-gray-500">
              Set the warehouse, pickup, and dispatch locations used for shipping.
            </p>
          </Link>

          <section className="rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm">
            <h2 className="text-lg font-bold text-gray-900">Current coverage</h2>
            <div className="mt-4 space-y-3 text-sm text-gray-700">
              <CoverageRow label="Country" value={country} />
              <CoverageRow label="State / Region" value={region} />
              <CoverageRow label="City" value={city} />
            </div>
          </section>
        </aside>
      </div>
    </div>
  );
}

function SelectField({
  label,
  value,
  options,
  onChange,
  icon,
}: {
  label: string;
  value: string;
  options: readonly string[];
  onChange: (value: string) => void;
  icon?: React.ReactNode;
}) {
  return (
    <label className="block">
      <span className="mb-2 block text-sm font-semibold text-[#374151]">{label}</span>
      <div className="relative flex items-center gap-3 rounded-xl border border-[#d1d5db] bg-white px-4 py-3 transition-all focus-within:border-[#114f8f] focus-within:ring-4 focus-within:ring-blue-50/50">
        {icon}
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

function CoverageRow({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-2xl border border-[#eef1f4] bg-[#f9fafb] px-4 py-3">
      <div className="text-[12px] font-bold uppercase tracking-[0.14em] text-gray-400">
        {label}
      </div>
      <div className="mt-1 text-sm font-medium text-gray-900">{value}</div>
    </div>
  );
}
