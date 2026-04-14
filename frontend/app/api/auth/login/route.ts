import { NextRequest } from "next/server";
import { proxyToLaravel } from "@/lib/proxy";

export async function POST(request: NextRequest) {
  return proxyToLaravel("POST", "/auth/login", request);
}
