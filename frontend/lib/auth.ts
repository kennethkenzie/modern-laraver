/**
 * Client-side auth helpers backed by the Laravel API.
 *
 * Tokens are stored in localStorage (remember me) or sessionStorage (session only).
 * The profile object mirrors the shape returned by GET /api/auth/me.
 */

import { normalizePhoneNumber } from "@/lib/phone";

const API =
  (typeof window !== "undefined"
    ? process.env.NEXT_PUBLIC_API_URL
    : process.env.API_URL) ?? "https://admin.e-modern.ug/api";

const SESSION_KEY = "modern_session_v1";
const TOKEN_KEY   = "admin_token";

export type CustomerProfile = {
  id?: string;
  fullName: string;
  email?: string;
  phone?: string;
  address?: string;
  city?: string;
  country?: string;
  role?: string;
};

// ─── token helpers ──────────────────────────────────────────

function isBrowser() {
  return typeof window !== "undefined";
}

export function getToken(): string | null {
  if (!isBrowser()) return null;
  return localStorage.getItem(TOKEN_KEY) || sessionStorage.getItem(TOKEN_KEY) || null;
}

function setToken(token: string, remember: boolean) {
  const storage = remember ? localStorage : sessionStorage;
  storage.setItem(TOKEN_KEY, token);
}

function clearToken() {
  if (!isBrowser()) return;
  localStorage.removeItem(TOKEN_KEY);
  sessionStorage.removeItem(TOKEN_KEY);
}

// ─── session helpers ─────────────────────────────────────────

function setSession(profile: CustomerProfile) {
  if (!isBrowser()) return;
  localStorage.setItem(SESSION_KEY, JSON.stringify(profile));
  window.dispatchEvent(new Event("auth:updated"));
}

export function getCurrentUser(): CustomerProfile | null {
  if (!isBrowser()) return null;
  try {
    const raw = localStorage.getItem(SESSION_KEY);
    return raw ? (JSON.parse(raw) as CustomerProfile) : null;
  } catch {
    return null;
  }
}

export function isLoggedIn() {
  return !!getCurrentUser();
}

// ─── API helpers ─────────────────────────────────────────────

async function apiPost<T>(path: string, body: unknown, token?: string): Promise<T> {
  const res = await fetch(`${API}${path}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: JSON.stringify(body),
  });
  return res.json() as Promise<T>;
}

// ─── public auth functions ────────────────────────────────────

export async function login(emailOrPhone: string, password: string) {
  const value = emailOrPhone.trim();
  // Use email field when the value looks like an email, otherwise phone
  const isEmail = value.includes("@");
  const payload = isEmail
    ? { email: value.toLowerCase(), password }
    : { phone: normalizePhoneNumber(value) || value, password };

  const data = await apiPost<{ ok?: boolean; token?: string; profile?: CustomerProfile; error?: string }>(
    "/auth/login",
    payload
  );

  if (data.error || !data.ok) {
    return { ok: false as const, error: data.error ?? "Login failed." };
  }

  setToken(data.token!, false);
  const profile: CustomerProfile = {
    id:       (data.profile as any)?.id,
    fullName: (data.profile as any)?.fullName ?? "",
    email:    (data.profile as any)?.email,
    phone:    (data.profile as any)?.phone,
    role:     (data.profile as any)?.role,
  };
  setSession(profile);
  return { ok: true as const, user: profile };
}

export async function register(input: CustomerProfile & { password: string }) {
  const phone = normalizePhoneNumber(input.phone ?? "") || (input.phone ?? "").trim();

  if (!phone) {
    return { ok: false as const, error: "Phone number is required." };
  }

  const data = await apiPost<{ ok?: boolean; token?: string; profile?: CustomerProfile; error?: string }>(
    "/auth/register",
    {
      full_name: input.fullName.trim(),
      phone,
      email:    input.email?.trim().toLowerCase() || undefined,
      password: input.password,
    }
  );

  if (data.error || !data.ok) {
    return { ok: false as const, error: data.error ?? "Registration failed." };
  }

  setToken(data.token!, false);
  const profile: CustomerProfile = {
    id:       (data.profile as any)?.id,
    fullName: (data.profile as any)?.fullName ?? "",
    email:    (data.profile as any)?.email,
    phone:    (data.profile as any)?.phone,
    role:     (data.profile as any)?.role,
  };
  setSession(profile);
  return { ok: true as const, user: profile };
}

export async function signInWithPhone(phone: string) {
  // After OTP verification, try to find the account by phone (no password).
  // Falls back to the localStorage-only approach if the account does not exist on the server.
  const normalized = normalizePhoneNumber(phone) || phone.trim();
  const current = getCurrentUser();
  if (current?.phone === normalized) return { ok: true as const, user: current };
  return { ok: false as const, error: "No account found for that phone number." };
}

export async function signInWithPhoneOrEmail(phone: string, email?: string) {
  return signInWithPhone(phone);
}

export async function signUpWithPhone(fullName: string, phone: string, email: string) {
  const normalized = normalizePhoneNumber(phone) || phone.trim();
  if (!normalized) return { ok: false as const, error: "Phone number is required." };
  if (!email.trim()) return { ok: false as const, error: "Email is required." };

  const data = await apiPost<{ ok?: boolean; token?: string; profile?: CustomerProfile; error?: string }>(
    "/auth/register",
    { full_name: fullName.trim(), phone: normalized, email: email.trim().toLowerCase() }
  );

  if (data.error || !data.ok) {
    return { ok: false as const, error: data.error ?? "Registration failed." };
  }

  setToken(data.token!, false);
  const profile: CustomerProfile = {
    id:       (data.profile as any)?.id,
    fullName: (data.profile as any)?.fullName ?? "",
    email:    (data.profile as any)?.email,
    phone:    (data.profile as any)?.phone,
    role:     (data.profile as any)?.role,
  };
  setSession(profile);
  return { ok: true as const, user: profile };
}

export async function logout() {
  if (!isBrowser()) return;

  const token = getToken();
  if (token) {
    await fetch(`${API}/auth/logout`, {
      method: "POST",
      headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
    }).catch(() => {});
  }

  clearToken();
  localStorage.removeItem(SESSION_KEY);
  window.dispatchEvent(new Event("auth:updated"));
}

export function updateCurrentUser(patch: Partial<CustomerProfile>) {
  const current = getCurrentUser();
  if (!current) return null;
  const next: CustomerProfile = { ...current, ...patch };
  setSession(next);
  return next;
}
