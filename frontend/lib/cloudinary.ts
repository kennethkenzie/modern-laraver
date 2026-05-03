/**
 * Image utilities.
 *
 * Images are stored in Laravel local storage and served from admin.e-modern.ug.
 * toCloudinaryUrl ensures every URL is absolute so Next.js <Image> can
 * proxy + cache + compress it through the Vercel CDN.
 */

const BACKEND_BASE = "https://admin.e-modern.ug";

/**
 * Ensures an image URL is absolute.
 * Relative paths (e.g. /storage/products/img.jpg) are prefixed with the
 * backend base URL so Next.js image optimisation can fetch and cache them.
 */
export function toCloudinaryUrl(url: string): string {
  if (!url) return "";
  // Already absolute
  if (url.startsWith("http://") || url.startsWith("https://")) return url;
  // Relative path — make it absolute
  if (url.startsWith("/")) return `${BACKEND_BASE}${url}`;
  return `${BACKEND_BASE}/${url}`;
}

/** No fallback image — return empty string so nothing is shown on error. */
export function getImageFallbackUrl(_url: string): string {
  return "";
}

export function getCloudinaryCloudName() {
  return "";
}
