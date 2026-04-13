
// Use dynamic import for dotenv to avoid issues if not installed
import 'dotenv/config';
import { prisma } from '../lib/prisma';

async function main() {
  try {
    const categories = await prisma.category.findMany();
    console.log('--- CATEGORY TABLE ---');
    console.log(`Found ${categories.length} categories in Category model.`);
    if (categories.length > 0) {
      console.log('First 3 categories:', JSON.stringify(categories.slice(0, 3), null, 2));
    }

    const settings = await prisma.siteSettings.findUnique({
      where: { key: 'frontend_data' }
    });
    
    console.log('\n--- SITESETTINGS (frontend_data) ---');
    const frontendCategories = (settings?.value as any)?.categories || [];
    console.log(`Found ${frontendCategories.length} categories in SiteSettings JSON.`);
    if (frontendCategories.length > 0) {
      console.log('First 3 categories:', JSON.stringify(frontendCategories.slice(0, 3), null, 2));
    }
  } catch (err) {
    console.error('Error during database check:', err);
  }
}

main()
  .finally(() => process.exit(0));
