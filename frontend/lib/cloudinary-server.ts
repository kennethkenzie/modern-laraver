import "server-only";

import crypto from "node:crypto";
import { v2 as cloudinary } from "cloudinary";

function readEnv(name: string) {
  return process.env[name]?.trim() || "";
}

export function getCloudinaryServerConfig() {
  const cloudName = readEnv("NEXT_PUBLIC_CLOUDINARY_CLOUD_NAME");
  const apiKey = readEnv("CLOUDINARY_API_KEY");
  const apiSecret = readEnv("CLOUDINARY_API_SECRET");
  const uploadPreset = readEnv("CLOUDINARY_UPLOAD_PRESET");

  return {
    cloudName,
    apiKey,
    apiSecret,
    uploadPreset,
    configured: Boolean(cloudName && apiKey && apiSecret),
  };
}

export function assertCloudinaryServerConfig() {
  const config = getCloudinaryServerConfig();

  if (!config.cloudName || !config.apiKey || !config.apiSecret) {
    throw new Error(
      "Missing Cloudinary configuration. Set NEXT_PUBLIC_CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, and CLOUDINARY_API_SECRET."
    );
  }

  return config;
}

export function configureCloudinary() {
  const config = assertCloudinaryServerConfig();

  cloudinary.config({
    cloud_name: config.cloudName,
    api_key: config.apiKey,
    api_secret: config.apiSecret,
    secure: true,
  });

  return cloudinary;
}

export function signCloudinaryParams(params: Record<string, string | number>) {
  const { apiSecret } = assertCloudinaryServerConfig();

  const payload = Object.entries(params)
    .filter(([, value]) => value !== "" && value !== null && value !== undefined)
    .sort(([a], [b]) => a.localeCompare(b))
    .map(([key, value]) => `${key}=${value}`)
    .join("&");

  return crypto.createHash("sha1").update(`${payload}${apiSecret}`).digest("hex");
}
