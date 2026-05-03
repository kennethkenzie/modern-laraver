"use client";
import { useEffect } from "react";
import { useRouter, useSearchParams } from "next/navigation";

export default function RegisterPageClient() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const redirect = searchParams.get("redirect") || "/user";

  useEffect(() => {
    router.replace(`/login?redirect=${encodeURIComponent(redirect)}`);
  }, [router, redirect]);

  return null;
}
