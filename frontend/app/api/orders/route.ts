import { NextRequest } from "next/server";
import { proxyToLaravel } from "@/lib/proxy";

export async function GET(request: NextRequest) {
  return proxyToLaravel("GET", "/orders", request);
}

export async function POST(request: NextRequest) {
  return proxyToLaravel("POST", "/orders", request);
}
