import { NextRequest } from "next/server";
import { proxyToLaravel } from "@/lib/proxy";

type Context = { params: Promise<{ id: string }> };

export async function GET(_: NextRequest, { params }: Context) {
  const { id } = await params;
  return proxyToLaravel("GET", `/admin/products/${id}`);
}

export async function PATCH(request: NextRequest, { params }: Context) {
  const { id } = await params;
  return proxyToLaravel("PATCH", `/admin/products/${id}`, request);
}

export async function DELETE(_: NextRequest, { params }: Context) {
  const { id } = await params;
  return proxyToLaravel("DELETE", `/admin/products/${id}`);
}
