/**
 * Images are stored in and served directly from Laravel public storage.
 * No external CDN or Cloudinary transformation is applied.
 * toCloudinaryUrl is kept as a passthrough so existing call sites compile unchanged.
 */

function buildPlaceholderLabel(url: string) {
  try {
    const parsed = new URL(url);
    const raw =
      parsed.pathname.split("/").filter(Boolean).pop()?.split(".")[0] ??
      "modern electronics";

    return raw
      .replace(/[-_]+/g, " ")
      .replace(/%20/g, " ")
      .trim()
      .slice(0, 32);
  } catch {
    return "modern electronics";
  }
}

function buildPlaceholderDataUri(url: string) {
  const label = buildPlaceholderLabel(url) || "modern electronics";
  const seed = Array.from(label).reduce((acc, char) => acc + char.charCodeAt(0), 0);
  const hue = seed % 360;
  const accent = (hue + 36) % 360;
  const svg = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 900" role="img" aria-label="${label}">
      <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="hsl(${hue} 70% 96%)" />
          <stop offset="100%" stop-color="hsl(${accent} 75% 88%)" />
        </linearGradient>
      </defs>
      <rect width="1200" height="900" fill="url(#bg)" />
      <circle cx="980" cy="180" r="140" fill="hsl(${accent} 80% 80% / 0.45)" />
      <circle cx="220" cy="720" r="190" fill="hsl(${hue} 70% 75% / 0.30)" />
      <text x="80" y="430" fill="#111827" font-family="Arial, Helvetica, sans-serif" font-size="72" font-weight="700">
        Modern Electronics
      </text>
      <text x="80" y="510" fill="#374151" font-family="Arial, Helvetica, sans-serif" font-size="36">
        ${label}
      </text>
    </svg>
  `;

  return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(
    svg.replace(/\s+/g, " ").trim()
  )}`;
}

/** Returns the placeholder SVG when an image URL fails to load. */
export function getImageFallbackUrl(url: string) {
  return buildPlaceholderDataUri(url);
}

/** Passthrough — images are served directly from Laravel storage. */
export function toCloudinaryUrl(url: string): string {
  return url;
}

export function getCloudinaryCloudName() {
  return "";
}
