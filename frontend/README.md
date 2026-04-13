This is a [Next.js](https://nextjs.org) project bootstrapped with [`create-next-app`](https://nextjs.org/docs/app/api-reference/cli/create-next-app).

## Getting Started

First, run the development server:

```bash
npm run dev
# or
yarn dev
# or
pnpm dev
# or
bun dev
```

Open [http://localhost:3000](http://localhost:3000) with your browser to see the result.

### Cloudinary setup

Set these variables in `.env.local`:

```bash
NEXT_PUBLIC_CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_cloudinary_api_key
CLOUDINARY_API_SECRET=your_rotated_cloudinary_api_secret
CLOUDINARY_UPLOAD_PRESET=Modern
```

`NEXT_PUBLIC_CLOUDINARY_CLOUD_NAME` is used for optimized image delivery in the frontend.

`CLOUDINARY_API_KEY` and `CLOUDINARY_API_SECRET` are server-only credentials for signed uploads or admin media actions. Do not expose them to the client.

If your Cloudinary secret was shared anywhere, rotate it in Cloudinary before adding it to `.env.local`.

For admin uploads, use `POST /api/admin/cloudinary-signature`. It returns:

```json
{
  "cloudName": "your_cloud_name",
  "apiKey": "your_api_key",
  "timestamp": 1710000000,
  "folder": "modern/admin",
  "uploadPreset": "Modern",
  "publicId": null,
  "tags": null,
  "signature": "signed_hash",
  "uploadUrl": "https://api.cloudinary.com/v1_1/your_cloud_name/auto/upload"
}
```

Send an optional JSON body like:

```json
{
  "folder": "modern/products",
  "publicId": "product-main-image",
  "tags": ["admin", "product"]
}
```

If your Cloudinary account uses the `Modern` preset, set `CLOUDINARY_UPLOAD_PRESET=Modern` in `.env.local` so the signature route includes it automatically.

### Prisma Database Setup

The storefront content now reads from your database using [Prisma](https://www.prisma.io/).

Add this variable to your `.env` or `.env.local`:

```bash
DATABASE_URL="postgresql://postgres:[PASSWORD]@db.[PROJECT_ID].supabase.co:5432/postgres?pgbouncer=true"
```

1. **Generate Prisma Client**:
   ```bash
   npx prisma generate
   ```

2. **Push Schema to Database** (if starting fresh):
   ```bash
   npx prisma db push
   ```

The application uses `lib/prisma.ts` for a shared Prisma client and `lib/site-settings.ts` for managing site-wide configuration.


You can start editing the page by modifying `app/page.tsx`. The page auto-updates as you edit the file.

This project uses [`next/font`](https://nextjs.org/docs/app/building-your-application/optimizing/fonts) to automatically optimize and load [Geist](https://vercel.com/font), a new font family for Vercel.

## Learn More

To learn more about Next.js, take a look at the following resources:

- [Next.js Documentation](https://nextjs.org/docs) - learn about Next.js features and API.
- [Learn Next.js](https://nextjs.org/learn) - an interactive Next.js tutorial.

You can check out [the Next.js GitHub repository](https://github.com/vercel/next.js) - your feedback and contributions are welcome!

## Deploy on Vercel

The easiest way to deploy your Next.js app is to use the [Vercel Platform](https://vercel.com/new?utm_medium=default-template&filter=next.js&utm_source=create-next-app&utm_campaign=create-next-app-readme) from the creators of Next.js.

Check out our [Next.js deployment documentation](https://nextjs.org/docs/app/building-your-application/deploying) for more details.
