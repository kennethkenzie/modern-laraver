"use client";

import { useEffect, useState, type ImgHTMLAttributes } from "react";
import { getImageFallbackUrl, toCloudinaryUrl } from "@/lib/cloudinary";

type SafeImageProps = Omit<ImgHTMLAttributes<HTMLImageElement>, "src"> & {
  src: string;
};

export default function SafeImage({ src, alt, ...props }: SafeImageProps) {
  const [resolvedSrc, setResolvedSrc] = useState(() => toCloudinaryUrl(src));

  useEffect(() => {
    setResolvedSrc(toCloudinaryUrl(src));
  }, [src]);

  return (
    <img
      {...props}
      src={resolvedSrc}
      alt={alt}
      onError={() => {
        setResolvedSrc(getImageFallbackUrl(src));
      }}
    />
  );
}
