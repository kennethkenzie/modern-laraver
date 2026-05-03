/**
 * site-settings.ts - delegates all reads/writes to the Laravel backend.
 * Previously called Prisma directly; now uses the REST API.
 */
import { API_URL, ADMIN_API_TOKEN } from "@/lib/api";
import { mergeFrontendData } from "@/lib/frontend-data-merge";
import type { FrontendData } from "@/lib/frontend-data";

export async function readFrontendDataFromPrisma(): Promise<FrontendData | null> {
  try {
    const response = await fetch(`${API_URL}/frontend-data`, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
      // ISR: revalidate every 5 minutes instead of no-store, so static routes
      // (/_not-found, /about, etc.) can be pre-rendered at build time and
      // refreshed incrementally — avoids DYNAMIC_SERVER_USAGE errors.
      next: { revalidate: 300 },
    });

    if (!response.ok) {
      return null;
    }

    const payload = (await response.json()) as { data?: Partial<FrontendData> };
    return mergeFrontendData(payload.data ?? {});
  } catch (error) {
    console.error("Failed to read frontend data from API:", error);
    return null;
  }
}

export async function writeFrontendDataToPrisma(data: FrontendData): Promise<FrontendData> {
  try {
    const response = await fetch(`${API_URL}/admin/frontend-data`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        ...(ADMIN_API_TOKEN ? { Authorization: `Bearer ${ADMIN_API_TOKEN}` } : {}),
      },
      body: JSON.stringify(data),
      cache: "no-store",
    });

    if (!response.ok) {
      const errorBody = (await response.json().catch(() => ({ error: response.statusText }))) as {
        error?: string;
      };
      throw new Error(errorBody.error ?? response.statusText);
    }

    const result = (await response.json()) as { data?: Partial<FrontendData> };
    return mergeFrontendData(result.data ?? data);
  } catch (error) {
    console.error("Failed to write frontend data to API:", error);
    throw error;
  }
}
