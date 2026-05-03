"use client";

import NextImage from "next/image";
import { useState } from "react";
import { getImageFallbackUrl } from "@/lib/cloudinary";

type SafeImageProps = {
  src: string;
  alt: string;
  className?: string;
  /** Pass true when the parent is position:relative/absolute with defined size */
  fill?: boolean;
  /** Hint the browser this image is above the fold — skips lazy loading */
  priority?: boolean;
  /** Pass responsive sizes string for correct srcset selection */
  sizes?: string;
  width?: number;
  height?: number;
  loading?: "lazy" | "eager";
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
  quality = 80,
}: SafeImageProps) {
  const [imgSrc, setImgSrc] = useState(src || "/placeholder.png");

  const handleError = () => {
    if (src) setImgSrc(getImageFallbackUrl(src));
  };

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
