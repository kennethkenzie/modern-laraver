import type { Metadata } from "next";
import "./globals.css";
import MiniCart from "@/components/MiniCart";
import AuthOverlay from "@/components/AuthOverlay";

import { readFrontendDataFromPrisma } from "@/lib/site-settings";
import { mergeFrontendData } from "@/lib/frontend-data-merge";

export async function generateMetadata(): Promise<Metadata> {
  const prismaData = await readFrontendDataFromPrisma();
  const data = mergeFrontendData(prismaData);
  const nav = data.navbar;

  return {
    title: nav.siteTitle || "Modern Electronics",
    description: "Premium Electronics Store",
    icons: {
      icon: nav.faviconUrl || "/favicon.ico",
    },
  };
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className="antialiased">
        {children}
        <MiniCart />
        <AuthOverlay />
      </body>
    </html>
  );
}
