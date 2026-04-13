"use client";

import { useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { Loader2, Mail, Phone, User, X } from "lucide-react";
import { useRouter } from "next/navigation";
import { signInWithPhoneOrEmail, signUpWithPhone } from "@/lib/auth";

type AuthMode = "login" | "register";
type AuthStep = "details" | "otp";
type AuthModalDetail = {
  mode?: AuthMode;
  redirect?: string;
};

const DEFAULT_REDIRECT = "/user";

export default function AuthOverlay() {
  const router = useRouter();
  const [isOpen, setIsOpen] = useState(false);
  const [mode, setMode] = useState<AuthMode>("login");
  const [step, setStep] = useState<AuthStep>("details");
  const [redirect, setRedirect] = useState(DEFAULT_REDIRECT);
  const [fullName, setFullName] = useState("");
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [otp, setOtp] = useState("");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");

  const isRegister = mode === "register";

  const canSubmit = useMemo(() => {
    if (step === "otp") {
      return otp.trim().length > 0;
    }

    if (isRegister) {
      return fullName.trim().length > 0 && email.trim().length > 0 && phone.trim().length > 0;
    }

    return phone.trim().length > 0;
  }, [email, fullName, isRegister, otp, phone, step]);

  const resetState = (nextMode: AuthMode = mode) => {
    setMode(nextMode);
    setStep("details");
    setOtp("");
    setError("");
    if (nextMode === "login") {
      setFullName("");
    }
  };

  const closeModal = () => {
    setIsOpen(false);
    setBusy(false);
    setStep("details");
    setOtp("");
    setError("");
    window.dispatchEvent(new Event("auth:modal-close"));
  };

  useEffect(() => {
    const openModal = (detail?: AuthModalDetail) => {
      const nextMode = detail?.mode === "register" ? "register" : "login";
      setRedirect(detail?.redirect || DEFAULT_REDIRECT);
      setIsOpen(true);
      resetState(nextMode);
    };

    const handleModalOpen = (event: Event) => {
      const customEvent = event as CustomEvent<AuthModalDetail>;
      openModal(customEvent.detail);
    };

    const handleLegacyOpen = (event: Event) => {
      const customEvent = event as CustomEvent<{ view?: "login" | "register" }>;
      openModal({
        mode: customEvent.detail?.view === "register" ? "register" : "login",
      });
    };

    const handleClose = () => closeModal();

    window.addEventListener("auth:modal-open", handleModalOpen as EventListener);
    window.addEventListener("auth:open-overlay", handleLegacyOpen as EventListener);
    window.addEventListener("auth:close-overlay", handleClose);

    return () => {
      window.removeEventListener("auth:modal-open", handleModalOpen as EventListener);
      window.removeEventListener("auth:open-overlay", handleLegacyOpen as EventListener);
      window.removeEventListener("auth:close-overlay", handleClose);
    };
  }, [mode]);

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    const handleEscape = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        closeModal();
      }
    };

    window.addEventListener("keydown", handleEscape);
    return () => {
      window.removeEventListener("keydown", handleEscape);
    };
  }, [isOpen]);

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

      const result = await (isRegister
        ? signUpWithPhone(fullName, phone, email)
        : signInWithPhoneOrEmail(phone, email));

      if (!result.ok) {
        setError(result.error);
        return;
      }

      closeModal();
      router.push(redirect);
    } catch {
      setError("Failed to verify OTP.");
    } finally {
      setBusy(false);
    }
  }

  if (!isOpen) {
    return null;
  }

  return (
    <div className="fixed inset-0 z-[2000] flex items-center justify-center bg-black/55 px-4 backdrop-blur-[2px]">
      <button
        type="button"
        aria-label="Close sign in"
        onClick={closeModal}
        className="absolute inset-0"
      />

      <div className="relative w-full max-w-[420px] rounded-[20px] border border-[#d5d9d9] bg-white shadow-[0_18px_40px_rgba(15,17,17,0.28)]">
        <button
          type="button"
          onClick={closeModal}
          className="absolute right-4 top-4 inline-flex h-8 w-8 items-center justify-center rounded-full text-[#565959] hover:bg-[#f3f3f3] hover:text-[#0f1111]"
        >
          <X size={18} />
        </button>

        <div className="border-b border-[#eaeded] px-6 py-5">
          <div className="text-[24px] font-semibold leading-none text-[#0f1111]">
            {isRegister ? "Create account" : "Sign in"}
          </div>
          <p className="mt-2 text-[13px] leading-5 text-[#565959]">
            {isRegister
              ? "Create your customer account and verify it with a one-time SMS code."
              : "Use your phone number and verify with a one-time SMS code."}
          </p>
        </div>

        <div className="px-6 py-6">
          <div className="mb-5 inline-flex rounded-full border border-[#d5d9d9] bg-[#f7fafa] p-1 text-[13px]">
            <button
              type="button"
              onClick={() => resetState("login")}
              className={`rounded-full px-4 py-2 font-medium transition ${
                !isRegister ? "bg-[#ffd814] text-[#0f1111]" : "text-[#565959]"
              }`}
            >
              Sign in
            </button>
            <button
              type="button"
              onClick={() => resetState("register")}
              className={`rounded-full px-4 py-2 font-medium transition ${
                isRegister ? "bg-[#ffd814] text-[#0f1111]" : "text-[#565959]"
              }`}
            >
              Create account
            </button>
          </div>

          <div className="space-y-4">
            {isRegister && step === "details" ? (
              <>
                <Field
                  label="Full name"
                  value={fullName}
                  onChange={setFullName}
                  placeholder="Your full name"
                  icon={<User size={18} className="text-[#565959]" />}
                  type="text"
                />
                <Field
                  label="Email"
                  value={email}
                  onChange={setEmail}
                  placeholder="you@example.com"
                  icon={<Mail size={18} className="text-[#565959]" />}
                  type="email"
                />
              </>
            ) : null}

            {!isRegister && step === "details" ? (
              <Field
                label="Email (optional)"
                value={email}
                onChange={setEmail}
                placeholder="you@example.com"
                icon={<Mail size={18} className="text-[#565959]" />}
                type="email"
              />
            ) : null}

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

            {step === "details" ? (
              <button
                type="button"
                disabled={busy || !canSubmit}
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
                  disabled={busy || !canSubmit}
                  onClick={() => void verifyOtp()}
                  className="flex w-full items-center justify-center gap-2 rounded-full border border-[#fcd200] bg-[#ffd814] px-4 py-3 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] hover:bg-[#f7ca00] disabled:opacity-60"
                >
                  {busy ? <Loader2 size={16} className="animate-spin" /> : null}
                  {isRegister ? "Verify and create account" : "Verify and sign in"}
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setStep("details");
                    setOtp("");
                    setError("");
                  }}
                  className="w-full text-[13px] font-medium text-[#007185] hover:underline"
                >
                  Change {isRegister ? "details" : "phone number"}
                </button>
              </div>
            )}
          </div>

          <div className="mt-6 border-t border-[#eaeded] pt-5 text-[12px] leading-5 text-[#565959]">
            By continuing, you agree to Modern Electronics account access for checkout, orders, and saved items.
          </div>

          <div className="mt-4 text-[13px] text-[#565959]">
            {isRegister ? "Already have an account?" : "New to Modern Electronics?"}{" "}
            <button
              type="button"
              onClick={() => resetState(isRegister ? "login" : "register")}
              className="font-medium text-[#007185] hover:underline"
            >
              {isRegister ? "Sign in instead" : "Create your account"}
            </button>
          </div>

          <div className="mt-4 text-[12px] text-[#565959]">
            <Link href={isRegister ? "/login" : "/register"} className="hover:underline">
              Open this form on its own page
            </Link>
          </div>
        </div>
      </div>
    </div>
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
