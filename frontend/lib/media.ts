export function buildMediaProxyUrl(path: string): string {
  return `/api/media/${path.replace(/^\/+/, "")}`;
}

export function normalizeMediaUrl(url: string): string {
  if (!url) return url;
  if (url.startsWith("blob:") || url.startsWith("data:") || url.startsWith("/api/media/")) {
    return url;
  }

  if (url.startsWith("/storage/")) {
    return buildMediaProxyUrl(url.replace(/^\/storage\//, ""));
  }

  try {
    const parsed = new URL(url);
    const storageMatch = parsed.pathname.match(/^\/storage\/(.+)$/);
    const mediaMatch = parsed.pathname.match(/^\/api\/media\/(.+)$/);

    if (storageMatch) return buildMediaProxyUrl(storageMatch[1]);
    if (mediaMatch) return buildMediaProxyUrl(mediaMatch[1]);
  } catch {
    return url;
  }

  return url;
}
