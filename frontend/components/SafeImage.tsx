"use client";

import NextImage from "next/image";
import { useState } from "react";
import { normalizeMediaUrl } from "@/lib/media";

type SafeImageProps = {
  src: string;
  alt: string;
  className?: string;
  /** Use when the parent has position:relative/absolute with defined dimensions */
  fill?: boolean;
  /** Above-the-fold images — skips lazy loading */
  priority?: boolean;
  /** Responsive sizes hint for correct srcset selection */
  sizes?: string;
  width?: number;
  height?: number;
  quality?: number;
};

export default function SafeImage({
  src,
  alt,
  className,
  fill,
  priority = false,
  sizes,
  width,
  height,
  quality = 82,
}: SafeImageProps) {
  const resolved = normalizeMediaUrl(src);
  const [imgSrc, setImgSrc] = useState(resolved);
  const [failed, setFailed] = useState(false);

  // Don't render anything if the URL is empty or the image failed to load
  if (!imgSrc || failed) {
    return <div className={className} aria-hidden="true" />;
  }

  const handleError = () => setFailed(true);

  if (fill) {
    return (
      <NextImage
        src={imgSrc}
        alt={alt ?? ""}
        fill
        className={className}
        priority={priority}
        sizes={sizes ?? "100vw"}
        quality={quality}
        onError={handleError}
      />
    );
  }

  return (
    <NextImage
      src={imgSrc}
      alt={alt ?? ""}
      width={width ?? 800}
      height={height ?? 800}
      className={className}
      priority={priority}
      sizes={sizes ?? "100vw"}
      quality={quality}
      onError={handleError}
    />
  );
}
