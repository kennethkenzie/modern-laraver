/**
 * Central API client for the Laravel backend.
 *
 * All server-side calls use API_URL (internal).
 * All client-side calls use NEXT_PUBLIC_API_URL (public).
 */

export const API_URL =
  (typeof window === "undefined"
    ? process.env.API_URL
    : process.env.NEXT_PUBLIC_API_URL) ?? "https://admin.e-modern.ug/api";

/** Token stored in browser after login (localStorage or sessionStorage). */
export function getClientToken(): string | null {
  if (typeof window === "undefined") return null;
  return (
    localStorage.getItem("admin_token") ||
    sessionStorage.getItem("admin_token") ||
    null
  );
}

type FetchOptions = {
  method?: string;
  body?: unknown;
  token?: string | null;
  headers?: Record<string, string>;
};

/** Typed JSON fetch helper wrapping the Laravel API. */
export async function apiFetch<T = unknown>(
  path: string,
  { method = "GET", body, token, headers = {} }: FetchOptions = {}
): Promise<T> {
  const init: RequestInit = {
    method,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...headers,
    },
    cache: "no-store",
  };

  if (body !== undefined) {
    init.body = JSON.stringify(body);
  }

  const res = await fetch(`${API_URL}${path}`, init);

  if (!res.ok) {
    const err = await res.json().catch(() => ({ error: res.statusText }));
    throw Object.assign(new Error((err as { error?: string }).error ?? res.statusText), {
      status: res.status,
    });
  }

  return res.json() as Promise<T>;
}

/** Server-side admin token (from .env.local ADMIN_API_TOKEN). */
export const ADMIN_API_TOKEN = process.env.ADMIN_API_TOKEN ?? "";
