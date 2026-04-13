import { getProducts } from "@/lib/products-admin";
import ProductsListClient from "./ProductsListClient";

export default async function AdminProductsPage() {
  const products = await getProducts();

  return (
    <div className="bg-[#f7f7f8] min-h-screen p-4 sm:p-6 lg:p-8">
      <div className="mx-auto max-w-[1440px]">
        {/* Header Section */}
        <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-start gap-4">
            <span className="mt-2.5 h-3 w-10 rounded-full bg-[#0b63ce]" />
            <div>
              <h1 className="text-[32px] font-bold tracking-tight text-gray-900">All Products</h1>
              <p className="mt-1.5 text-[16px] text-gray-500 font-medium">
                Manage your catalog, stock levels, and product availability.
              </p>
            </div>
          </div>
          
          <div className="flex items-center gap-3">
            <div className="rounded-2xl border border-gray-200 bg-white px-5 py-3 shadow-sm">
                <div className="text-[12px] font-bold uppercase tracking-wider text-gray-400">Total Products</div>
                <div className="text-[24px] font-bold text-gray-900">{products.length}</div>
            </div>
          </div>
        </div>

        {/* Content Section */}
        <ProductsListClient initialProducts={products} />
      </div>
    </div>
  );
}
