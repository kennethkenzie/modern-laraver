import { prisma } from "./lib/prisma";


async function main() {
  const row = await prisma.siteSettings.findUnique({
    where: { key: "frontend_data" },
  });
  console.log("Current site settings in DB:");
  if (row) {
    const data = row.value as any;
    console.log("Brands count:", data?.brands?.length ?? 0);
    console.log("Brands:", data?.brands);
  } else {
    console.log("No site_settings found in DB.");
  }
}

main()
  .catch(console.error)
  .finally(() => prisma.$disconnect());
