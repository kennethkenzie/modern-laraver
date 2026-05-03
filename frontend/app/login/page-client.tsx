"use client";

import { Loader2, Lock, Mail, Phone } from "lucide-react";
import { useRouter, useSearchParams } from "next/navigation";
import { useState } from "react";
import { login } from "@/lib/auth";

export default function LoginPageClient() {
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [password, setPassword] = useState("");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");
  const router = useRouter();
  const searchParams = useSearchParams();
  const redirect = searchParams.get("redirect") || "/user";

  async function submitLogin() {
    setBusy(true);
    setError("");
    try {
      const result = await login(email.trim() || phone, password);
      if (!result.ok) { setError(result.error); return; }
      router.push(redirect);
    } catch {
      setError("Failed to sign in.");
    } finally {
      setBusy(false);
    }
  }

  return (
    <section className="min-h-screen bg-[#eaeded] px-4 py-10">
      <div className="mx-auto max-w-[420px]">
        <div className="mb-6 text-center">
          <div className="text-[28px] font-black tracking-tight text-[#0f1111]">Modern Electronics</div>
          <p className="mt-2 text-[13px] text-[#565959]">Secure sign in for checkout, orders, and saved items</p>
        </div>
        <div className="rounded-[18px] border border-[#d5d9d9] bg-white px-6 py-6 shadow-[0_8px_24px_rgba(15,17,17,0.12)]">
          <h1 className="text-[30px] font-normal leading-none text-[#0f1111]">Sign in</h1>
          <p className="mt-3 text-[14px] leading-6 text-[#565959]">Use your email or phone number and password.</p>
          <div className="mt-6 space-y-4">
            <Field label="Email (optional)" value={email} onChange={setEmail} placeholder="you@example.com" icon={<Mail size={18} className="text-[#565959]" />} type="email" />
            <Field label="Phone number" value={phone} onChange={setPhone} placeholder="+256..." icon={<Phone size={18} className="text-[#565959]" />} />
            <Field label="Password" value={password} onChange={setPassword} placeholder="Your password" icon={<Lock size={18} className="text-[#565959]" />} type="password" />
            {error ? <p className="text-[13px] text-[#b12704]">{error}</p> : null}
            <button type="button" disabled={busy || (!phone.trim() && !email.trim()) || !password} onClick={() => void submitLogin()}
              className="flex w-full items-center justify-center gap-2 rounded-full border border-[#fcd200] bg-[#ffd814] px-4 py-3 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] hover:bg-[#f7ca00] disabled:opacity-60">
              {busy ? <Loader2 size={16} className="animate-spin" /> : null}
              Sign in
            </button>
          </div>
        </div>
      </div>
    </section>
  );
}

function Field({ label, value, onChange, placeholder, icon, type = "tel" }: {
  label: string; value: string; onChange: (v: string) => void;
  placeholder: string; icon: React.ReactNode; type?: string;
}) {
  return (
    <label className="block">
      <span className="mb-1 block text-[13px] font-bold text-[#0f1111]">{label}</span>
      <div className="flex items-center gap-3 rounded-xl border border-[#a6a6a6] px-3 py-3 shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)] focus-within:border-[#007185]">
        {icon}
        <input type={type} value={value} onChange={(e) => onChange(e.target.value)} placeholder={placeholder}
          className="w-full bg-transparent text-[15px] text-[#0f1111] outline-none placeholder:text-[#8a8f98]" />
      </div>
    </label>
  );
}
