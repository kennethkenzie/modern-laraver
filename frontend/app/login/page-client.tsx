"use client";

import { Loader2, Lock, Mail, Phone, User } from "lucide-react";
import { useRouter, useSearchParams } from "next/navigation";
import { useRef, useState } from "react";
import { login, signup } from "@/lib/auth";

export default function LoginPageClient() {
  const [tab, setTab] = useState<"login" | "signup">("login");

  // shared fields
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [password, setPassword] = useState("");

  // signup-only fields
  const [fullName, setFullName] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");

  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const router = useRouter();
  const searchParams = useSearchParams();
  const redirect = searchParams.get("redirect") || "/user";

  function switchTab(next: "login" | "signup") {
    setTab(next);
    setError("");
    setSuccess("");
  }

  const loginRef = useRef(false);
  async function submitLogin() {
    if (loginRef.current) return;
    if (password.length < 8) { setError("Password must be at least 8 characters."); return; }
    loginRef.current = true;
    setBusy(true);
    setError("");
    try {
      const result = await login(email.trim() || phone, password);
      if (!result.ok) { setError(result.error); loginRef.current = false; return; }
      router.push(redirect);
    } catch {
      setError("Failed to sign in.");
    } finally {
      setBusy(false);
      loginRef.current = false;
    }
  }

  async function submitSignup() {
    if (!fullName.trim()) { setError("Full name is required."); return; }
    if (!phone.trim()) { setError("Phone number is required."); return; }
    if (password.length < 8) { setError("Password must be at least 8 characters."); return; }
    if (password !== confirmPassword) { setError("Passwords do not match."); return; }

    setBusy(true);
    setError("");
    try {
      const result = await signup(fullName.trim(), email.trim(), phone.trim(), password);
      if (!result.ok) { setError(result.error); return; }
      router.push(redirect);
    } catch {
      setError("Failed to create account.");
    } finally {
      setBusy(false);
    }
  }

  return (
    <section className="min-h-screen bg-[#eaeded] px-4 py-10">
      <div className="mx-auto max-w-[420px]">
        {/* Header */}
        <div className="mb-6 text-center">
          <div className="text-[28px] font-black tracking-tight text-[#0f1111]">Modern Electronics</div>
          <p className="mt-2 text-[13px] text-[#565959]">Secure account access for checkout, orders, and saved items</p>
        </div>

        <div className="rounded-[18px] border border-[#d5d9d9] bg-white shadow-[0_8px_24px_rgba(15,17,17,0.12)]">
          {/* Tabs */}
          <div className="flex border-b border-[#d5d9d9]">
            <button
              type="button"
              onClick={() => switchTab("login")}
              className={`flex-1 py-4 text-[14px] font-semibold transition-colors rounded-tl-[18px] ${
                tab === "login"
                  ? "border-b-2 border-[#007185] text-[#007185] bg-white"
                  : "text-[#565959] hover:bg-[#f7fafa]"
              }`}
            >
              Sign in
            </button>
            <button
              type="button"
              onClick={() => switchTab("signup")}
              className={`flex-1 py-4 text-[14px] font-semibold transition-colors rounded-tr-[18px] ${
                tab === "signup"
                  ? "border-b-2 border-[#007185] text-[#007185] bg-white"
                  : "text-[#565959] hover:bg-[#f7fafa]"
              }`}
            >
              Create account
            </button>
          </div>

          <div className="px-6 py-6">
            {tab === "login" ? (
              <>
                <p className="mb-5 text-[14px] leading-6 text-[#565959]">
                  Sign in with your email or phone number and password.
                </p>
                <div className="space-y-4">
                  <Field label="Email" value={email} onChange={setEmail} placeholder="you@example.com" icon={<Mail size={18} className="text-[#565959]" />} type="email" />
                  <Field label="Phone number" value={phone} onChange={v => setPhone(v.replace(/[^\d+]/g, ""))} placeholder="+256..." icon={<Phone size={18} className="text-[#565959]" />} type="tel" />
                  <Field label="Password" value={password} onChange={setPassword} placeholder="Min. 8 characters"

                  {error ? <p className="text-[13px] text-[#b12704]">{error}</p> : null}

                  <button
                    type="button"
                  disabled={busy || (!phone.trim() && !email.trim()) || password.length < 8}
                    onClick={() => void submitLogin()}
                    className="flex w-full items-center justify-center gap-2 rounded-full border border-[#fcd200] bg-[#ffd814] px-4 py-3 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] hover:bg-[#f7ca00] disabled:opacity-60"
                  >
                    {busy ? <Loader2 size={16} className="animate-spin" /> : null}
                    Sign in
                  </button>

                  <p className="text-center text-[13px] text-[#565959]">
                    New to Modern Electronics?{" "}
                    <button type="button" onClick={() => switchTab("signup")} className="font-semibold text-[#007185] hover:underline">
                      Create an account
                    </button>
                  </p>
                </div>
              </>
            ) : (
              <>
                <p className="mb-5 text-[14px] leading-6 text-[#565959]">
                  Create a free account to shop, track orders, and save items.
                </p>
                <div className="space-y-4">
                  <Field label="Full name" value={fullName} onChange={setFullName} placeholder="John Doe" icon={<User size={18} className="text-[#565959]" />} />
                  <Field label="Email (optional)" value={email} onChange={setEmail} placeholder="you@example.com" icon={<Mail size={18} className="text-[#565959]" />} type="email" />
                  <Field label="Phone number" value={phone} onChange={v => setPhone(v.replace(/[^\d+]/g, ""))} placeholder="+256..." icon={<Phone size={18} className="text-[#565959]" />} type="tel" />
                  <Field label="Password" value={password} onChange={setPassword} placeholder="Min. 8 characters"
                  <Field label="Confirm password" value={confirmPassword} onChange={setConfirmPassword} placeholder="Repeat your password" icon={<Lock size={18} className="text-[#565959]" />} type="password" />

                  {error ? <p className="text-[13px] text-[#b12704]">{error}</p> : null}
                  {success ? <p className="text-[13px] text-[#007600]">{success}</p> : null}

                  <button
                    type="button"
                  disabled={busy || !fullName.trim() || !phone.trim() || password.length < 8 || !confirmPassword}
                    onClick={() => void submitSignup()}
                    className="flex w-full items-center justify-center gap-2 rounded-full border border-[#fcd200] bg-[#ffd814] px-4 py-3 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] hover:bg-[#f7ca00] disabled:opacity-60"
                  >
                    {busy ? <Loader2 size={16} className="animate-spin" /> : null}
                    Create account
                  </button>

                  <p className="text-center text-[13px] text-[#565959]">
                    Already have an account?{" "}
                    <button type="button" onClick={() => switchTab("login")} className="font-semibold text-[#007185] hover:underline">
                      Sign in
                    </button>
                  </p>
                </div>
              </>
            )}
          </div>
        </div>

        <p className="mt-5 text-center text-[12px] text-[#767676]">
          By continuing you agree to our Terms of Service and Privacy Policy.
        </p>
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
        <input
          type={type}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          placeholder={placeholder}
          className="w-full bg-transparent text-[15px] text-[#0f1111] outline-none placeholder:text-[#8a8f98]"
        />
      </div>
    </label>
  );
}
