"use client";

import Link from "next/link";
import { Loader2, Mail, Phone } from "lucide-react";
import { useRouter, useSearchParams } from "next/navigation";
import { useState } from "react";
import { signInWithPhoneOrEmail } from "@/lib/auth";

export default function LoginPageClient() {
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [otp, setOtp] = useState("");
  const [step, setStep] = useState<"phone" | "otp">("phone");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");
  const router = useRouter();
  const searchParams = useSearchParams();
  const redirect = searchParams.get("redirect") || "/user";

  async function sendOtp() {
    setBusy(true);
    setError("");

    try {
      const response = await fetch("/api/auth/send-otp", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ phone }),
      });
      const payload = (await response.json()) as { error?: string };
      if (!response.ok) {
        setError(payload.error || "Failed to send OTP.");
        return;
      }
      setStep("otp");
    } catch {
      setError("Failed to send OTP.");
    } finally {
      setBusy(false);
    }
  }

  async function verifyOtp() {
    setBusy(true);
    setError("");

    try {
      const response = await fetch("/api/auth/verify-otp", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ phone, code: otp }),
      });
      const payload = (await response.json()) as { error?: string };
      if (!response.ok) {
        setError(payload.error || "Failed to verify OTP.");
        return;
      }

      const result = signInWithPhoneOrEmail(phone, email);
      if (!result.ok) {
        setError(result.error);
        return;
      }

      router.push(redirect);
    } catch {
      setError("Failed to verify OTP.");
    } finally {
      setBusy(false);
    }
  }

  return (
    <section className="min-h-screen bg-[#eaeded] px-4 py-10">
      <div className="mx-auto max-w-[420px]">
        <div className="mb-6 text-center">
          <div className="text-[28px] font-black tracking-tight text-[#0f1111]">
            Modern Electronics
          </div>
          <p className="mt-2 text-[13px] text-[#565959]">
            Secure sign in for checkout, orders, and saved items
          </p>
        </div>

        <div className="rounded-[18px] border border-[#d5d9d9] bg-white px-6 py-6 shadow-[0_8px_24px_rgba(15,17,17,0.12)]">
          <h1 className="text-[30px] font-normal leading-none text-[#0f1111]">
            Sign in
          </h1>
          <p className="mt-3 text-[14px] leading-6 text-[#565959]">
            Use your phone number and optionally your email, then verify with a one-time code.
          </p>

          <div className="mt-6 space-y-4">
            <Field
              label="Email (optional)"
              value={email}
              onChange={setEmail}
              placeholder="you@example.com"
              icon={<Mail size={18} className="text-[#565959]" />}
              type="email"
            />
            <Field
              label="Phone number"
              value={phone}
              onChange={setPhone}
              placeholder="+256..."
              icon={<Phone size={18} className="text-[#565959]" />}
            />

            {step === "otp" ? (
              <label className="block">
                <span className="mb-1 block text-[13px] font-bold text-[#0f1111]">
                  Verification code
                </span>
                <input
                  type="text"
                  value={otp}
                  onChange={(event) => setOtp(event.target.value)}
                  placeholder="123456"
                  className="h-11 w-full rounded-xl border border-[#a6a6a6] px-4 text-[15px] text-[#0f1111] shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)] outline-none focus:border-[#007185]"
                />
              </label>
            ) : null}

            {error ? <p className="text-[13px] text-[#b12704]">{error}</p> : null}

            {step === "phone" ? (
              <button
                type="button"
                disabled={busy || !phone.trim()}
                onClick={() => void sendOtp()}
                className="flex w-full items-center justify-center gap-2 rounded-full border border-[#fcd200] bg-[#ffd814] px-4 py-3 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] hover:bg-[#f7ca00] disabled:opacity-60"
              >
                {busy ? <Loader2 size={16} className="animate-spin" /> : null}
                Send OTP
              </button>
            ) : (
              <div className="space-y-3">
                <button
                  type="button"
                  disabled={busy || !otp.trim()}
                  onClick={() => void verifyOtp()}
                  className="flex w-full items-center justify-center gap-2 rounded-full border border-[#fcd200] bg-[#ffd814] px-4 py-3 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] hover:bg-[#f7ca00] disabled:opacity-60"
                >
                  {busy ? <Loader2 size={16} className="animate-spin" /> : null}
                  Verify and sign in
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setStep("phone");
                    setOtp("");
                    setError("");
                  }}
                  className="w-full text-[13px] font-medium text-[#007185] hover:underline"
                >
                  Change phone number
                </button>
              </div>
            )}
          </div>

          <div className="mt-6 border-t border-[#eaeded] pt-5">
            <p className="text-[13px] text-[#565959]">
              New to Modern Electronics?
            </p>
            <Link
              href={`/register?redirect=${encodeURIComponent(redirect)}`}
              className="mt-3 inline-flex w-full items-center justify-center rounded-full border border-[#d5d9d9] bg-[#f7fafa] px-4 py-3 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.08)] hover:bg-[#eef3f3]"
            >
              Create your account
            </Link>
          </div>
        </div>
      </div>
    </section>
  );
}

function Field({
  label,
  value,
  onChange,
  placeholder,
  icon,
  type = "tel",
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  placeholder: string;
  icon: React.ReactNode;
  type?: string;
}) {
  return (
    <label className="block">
      <span className="mb-1 block text-[13px] font-bold text-[#0f1111]">
        {label}
      </span>
      <div className="flex items-center gap-3 rounded-xl border border-[#a6a6a6] px-3 py-3 shadow-[inset_0_1px_2px_rgba(15,17,17,0.08)] focus-within:border-[#007185]">
        {icon}
        <input
          type={type}
          value={value}
          onChange={(event) => onChange(event.target.value)}
          placeholder={placeholder}
          className="w-full bg-transparent text-[15px] text-[#0f1111] outline-none placeholder:text-[#8a8f98]"
        />
      </div>
    </label>
  );
}
