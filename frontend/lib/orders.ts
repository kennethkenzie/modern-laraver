import { getToken } from "@/lib/auth";
import type { CartItem } from "@/lib/cart";

export type StorefrontOrder = {
  id: string;
  number: string;
  status: string;
  paymentStatus: string;
  fulfillmentMethod: "delivery" | "pickup";
  paymentMethod: string;
  subtotal: number;
  shipping: number;
  total: number;
  currencyCode: string;
  placedAt: string;
  items: {
    id: string;
    name: string;
    quantity: number;
    unitPrice: number;
    lineTotal: number;
    image?: string;
    href?: string;
  }[];
};

type PlaceOrderInput = {
  customer: {
    fullName: string;
    email?: string;
    phone?: string;
    address?: string;
    city?: string;
    country?: string;
  };
  fulfillmentMethod: "delivery" | "pickup";
  paymentMethod: string;
  pickupLocation?: unknown;
  items: CartItem[];
  subtotal: number;
  shipping: number;
  total: number;
};

async function authedFetch<T>(path: string, init: RequestInit = {}): Promise<T> {
  const token = getToken();
  const response = await fetch(path, {
    ...init,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...init.headers,
    },
  });

  const payload = (await response.json().catch(() => ({}))) as {
    error?: string;
    message?: string;
  };

  if (!response.ok) {
    throw new Error(payload.error ?? payload.message ?? response.statusText);
  }

  return payload as T;
}

export async function placeOrder(input: PlaceOrderInput) {
  const payload = await authedFetch<{ ok: boolean; order: StorefrontOrder }>("/api/orders", {
    method: "POST",
    body: JSON.stringify(input),
  });

  window.dispatchEvent(new Event("orders:updated"));
  return payload.order;
}

export async function fetchOrders() {
  const payload = await authedFetch<{ ok: boolean; orders: StorefrontOrder[] }>("/api/orders");
  return payload.orders ?? [];
}
