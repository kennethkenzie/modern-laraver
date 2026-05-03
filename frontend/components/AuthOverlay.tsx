"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { Loader2, Lock, Mail, Phone, X } from "lucide-react";
import { useRouter } from "next/navigation";
import { login } from "@/lib/auth";

type AuthModalDetail = {
  redirect?: string;
};

const DEFAULT_REDIRECT = "/user";

export default function AuthOverlay() {
  const router = useRouter();
  const [isOpen, setIsOpen] = useState(false);
  const [redirect, setRedirect] = useState(DEFAULT_REDIRECT);
  const [email, setEmail] = useState("");
  const [phone, setPhone] = useState("");
  const [password, setPassword] = useState("");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");

  const canSubmit = useMemo(
    () => (phone.trim().length > 0 || email.trim().length > 0) && password.length > 0,
    [email, password, phone]
  );

  const resetState = useCallback(() => {
    setPassword("");
    setError("");
  }, []);

  const closeModal = useCallback(() => {
    setIsOpen(false);
    setBusy(false);
    setPassword("");
    setError("");
    window.dispatchEvent(new Event("auth:modal-close"));
  }, []);

  useEffect(() => {
    const openModal = (detail?: AuthModalDetail) => {
      setRedirect(detail?.redirect || DEFAULT_REDIRECT);
      setIsOpen(true);
      resetState();
    };

    const handleModalOpen = (event: Event) => {
      const customEvent = event as CustomEvent<AuthModalDetail>;
      openModal(customEvent.detail);
    };

    const handleLegacyOpen = () => {
      openModal();
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
  }, [closeModal, resetState]);

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
  }, [closeModal, isOpen]);

  async function submitAuth() {
    setBusy(true);
    setError("");

    try {
      const result = await login(email.trim() || phone, password);

      if (!result.ok) {
        setError(result.error);
        return;
      }

      closeModal();
      router.push(redirect);
    } catch {
      setError("Failed to sign in.");
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
            Sign in
          </div>
          <p className="mt-2 text-[13px] leading-5 text-[#565959]">
            Use your email or phone number and password.
          </p>
        </div>

        <div className="px-6 py-6">
          <div className="space-y-4">
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

            <Field
              label="Password"
              value={password}
              onChange={setPassword}
              placeholder="Your password"
              icon={<Lock size={18} className="text-[#565959]" />}
              type="password"
            />

            {error ? <p className="text-[13px] text-[#b12704]">{error}</p> : null}

            <button
              type="button"
              disabled={busy || !canSubmit}
              onClick={() => void submitAuth()}
              className="flex w-full items-center justify-center gap-2 rounded-full border border-[#fcd200] bg-[#ffd814] px-4 py-3 text-[14px] font-medium text-[#0f1111] shadow-[inset_0_-1px_0_rgba(0,0,0,0.15)] hover:bg-[#f7ca00] disabled:opacity-60"
            >
              {busy ? <Loader2 size={16} className="animate-spin" /> : null}
              Sign in
            </button>
          </div>

          <div className="mt-6 border-t border-[#eaeded] pt-5 text-[12px] leading-5 text-[#565959]">
            By continuing, you agree to Modern Electronics account access for checkout, orders, and saved items.
          </div>

          <div className="mt-4 text-[12px] text-[#565959]">
            <Link href="/login" className="hover:underline">
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
