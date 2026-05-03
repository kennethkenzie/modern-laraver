import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  env: {
    NEXT_PUBLIC_API_URL: process.env.NEXT_PUBLIC_API_URL ?? "https://admin.e-modern.ug/api",
  },

  images: {
    // Allow product images from any host (Laravel storage, Cloudinary, etc.)
    remotePatterns: [
      { protocol: "https", hostname: "**" },
      { protocol: "http",  hostname: "**" },
    ],
    // Generate WebP + AVIF for supported browsers (~60-80% smaller than JPEG/PNG)
    formats: ["image/avif", "image/webp"],
    // Serve images at these breakpoints so browsers pick the right size
    deviceSizes: [375, 640, 750, 828, 1080, 1200, 1920],
    imageSizes: [16, 32, 64, 96, 128, 256, 384],
    // Cache optimized images for 30 days on the CDN
    minimumCacheTTL: 2592000,
  },

  async headers() {
    return [
      {
        // Preconnect to the backend image origin so the browser opens the
        // TCP connection before images are requested.
        source: "/",
        headers: [
          { key: "Link", value: "<https://admin.e-modern.ug>; rel=preconnect" },
          { key: "Link", value: "<https://res.cloudinary.com>; rel=preconnect" },
        ],
      },
      {
        source: "/api/:path*",
        headers: [
          { key: "Access-Control-Allow-Origin",  value: "*" },
          { key: "Access-Control-Allow-Methods", value: "GET,POST,PUT,PATCH,DELETE,OPTIONS" },
          { key: "Access-Control-Allow-Headers", value: "Content-Type,Authorization" },
        ],
      },
    ];
  },
};

export default nextConfig;
