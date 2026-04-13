const { PrismaClient } = require("@prisma/client");
const prisma = new PrismaClient();

async function main() {
  const row = await prisma.siteSettings.findUnique({
    where: { key: "frontend_data" }
  });
  console.log(JSON.stringify(row, null, 2));
}

main().catch(console.error).finally(() => prisma.$disconnect());
