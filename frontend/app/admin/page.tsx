import {
  AlertTriangle,
  ArrowDownRight,
  ArrowUpRight,
  Eye,
  RefreshCcw,
  Truck,
  Users,
} from "lucide-react";
import Link from "next/link";
import { getProducts } from "@/lib/products-admin";

type StatCard = {
  title: string;
  value: string;
  change: string;
  positive?: boolean;
  headline: string;
  subtext: string;
};

type StockRow = {
  id: string;
  product: string;
  category: string;
  stock: number;
  status: "Low" | "Critical";
};

type UploadRow = {
  id: string;
  name: string;
  category: string;
  price: string;
  status: "Draft" | "Published";
  createdAt: string;
};

type ActivityRow = {
  customer: string;
  action: string;
  time: string;
};

const customerActivity: ActivityRow[] = [
  {
    customer: "Kevin M.",
    action: "placed an order for TDA2822M IC",
    time: "5 min ago",
  },
  {
    customer: "Ruth A.",
    action: "added 3 products to cart",
    time: "12 min ago",
  },
  {
    customer: "Paul K.",
    action: "completed checkout",
    time: "20 min ago",
  },
  {
    customer: "Janet N.",
    action: "requested return for TV remote",
    time: "1 hour ago",
  },
  {
    customer: "Emma S.",
    action: "left a 5-star review",
    time: "2 hours ago",
  },
];

function formatUGX(value: number) {
  return `UGX ${value.toLocaleString("en-US")}`;
}

function formatDashboardDate(date: Date | string) {
  const normalizedDate = date instanceof Date ? date : new Date(date);

  if (Number.isNaN(normalizedDate.getTime())) {
    return "Unknown date";
  }

  return new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  }).format(normalizedDate);
}

function StatBadge({
  positive = true,
  value,
}: {
  positive?: boolean;
  value: string;
}) {
  return (
    <div
      className={`inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[13px] font-bold ${
        positive
          ? "border-green-100 bg-green-50 text-green-600"
          : "border-red-100 bg-red-50 text-red-600"
      }`}
    >
      {positive ? <ArrowUpRight size={14} strokeWidth={3} /> : <ArrowDownRight size={14} strokeWidth={3} />}
      {value}
    </div>
  );
}

function StatCard({ card }: { card: StatCard }) {
  return (
    <div className="group rounded-2xl border border-gray-200 bg-white p-7 shadow-sm transition-all hover:shadow-xl hover:shadow-blue-900/[0.03]">
      <div className="flex items-start justify-between gap-3">
        <h3 className="text-[16px] font-bold uppercase tracking-widest text-gray-400">{card.title}</h3>
        <StatBadge positive={card.positive} value={card.change} />
      </div>

      <div className="mt-4 text-[42px] font-black leading-none tracking-tight text-[#111827]">
        {card.value}
      </div>

      <div className="mt-6 flex items-center gap-2 text-[16px] font-bold text-[#114f8f]">
        <span>{card.headline}</span>
        {card.positive ? (
          <ArrowUpRight size={18} className="text-[#f6c400]" />
        ) : (
          <ArrowDownRight size={18} className="text-red-400" />
        )}
      </div>

      <p className="mt-2 text-[14px] font-medium text-gray-500">{card.subtext}</p>
    </div>
  );
}

function SalesChart() {
  const bars = [
    28, 22, 30, 35, 50, 58, 31, 62, 20, 40, 57, 38, 60, 27, 24, 22, 71, 56, 18,
    24, 30, 26, 57, 19, 66, 25, 70, 32, 49, 28, 71, 69, 84, 37, 25, 44, 53, 48,
    23, 18, 82, 61, 75, 38, 29, 17, 46, 41, 24, 72, 14, 52, 26, 73, 20, 67, 18,
    58, 55, 76, 22, 75, 25, 70, 47, 84, 20, 73, 28, 84, 17, 60, 80, 19, 69,
  ];

  const line = [
    12, 9, 18, 21, 28, 19, 25, 8, 12, 30, 17, 25, 14, 16, 11, 13, 35, 19, 10, 14,
    15, 19, 25, 8, 34, 12, 31, 15, 22, 12, 37, 41, 19, 14, 28, 21, 17, 10, 7, 39,
    24, 28, 26, 16, 8, 21, 17, 15, 37, 9, 20, 12, 34, 10, 27, 14, 24, 22, 39, 12,
    35, 14, 41, 9, 26, 16, 42, 10, 22, 14, 41, 11, 24, 39, 13, 31,
  ];

  const labels = [
    "Apr 5",
    "Apr 10",
    "Apr 15",
    "Apr 20",
    "Apr 25",
    "Apr 30",
    "May 5",
    "May 10",
    "May 15",
    "May 20",
    "May 25",
    "May 30",
    "Jun 4",
    "Jun 9",
    "Jun 14",
    "Jun 19",
    "Jun 24",
    "Jun 30",
  ];

  return (
    <div className="rounded-[28px] border border-[#e5e7eb] bg-white p-6 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
      <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <h3 className="text-[28px] font-semibold text-[#0b1220]">Sales Overview</h3>
          <p className="mt-1 text-[16px] text-[#7b8394]">
            Revenue and order activity for the last 3 months
          </p>
        </div>

        <div className="inline-flex w-full overflow-hidden rounded-2xl border border-[#e5e7eb] bg-[#fafafa] lg:w-auto">
          {["Last 3 months", "Last 30 days", "Last 7 days"].map((item, i) => (
            <button
              key={item}
              className={[
                "px-6 py-3 text-[15px] font-medium",
                i === 0 ? "bg-[#f3f4f6] text-[#111827]" : "text-[#111827]",
                i !== 0 ? "border-l border-[#e5e7eb]" : "",
              ].join(" ")}
            >
              {item}
            </button>
          ))}
        </div>
      </div>

      <div className="mt-8 h-[320px] w-full rounded-2xl bg-white">
        <svg viewBox="0 0 1200 320" className="h-full w-full">
          {[50, 100, 150, 200, 250].map((y) => (
            <line key={y} x1="0" y1={y} x2="1200" y2={y} stroke="#edf0f4" strokeWidth="1" />
          ))}

          <path
            d={
              bars
                .map((v, i) => {
                  const x = (i / (bars.length - 1)) * 1200;
                  const y = 260 - v * 2.2;
                  return `${i === 0 ? "M" : "L"} ${x} ${y}`;
                })
                .join(" ") + " L 1200 260 L 0 260 Z"
            }
            fill="rgba(17, 79, 143, 0.08)"
            stroke="#114f8f"
            strokeOpacity="0.3"
            strokeWidth="1.5"
          />

          <path
            d={line
              .map((v, i) => {
                const x = (i / (line.length - 1)) * 1200;
                const y = 260 - v * 2.8;
                return `${i === 0 ? "M" : "L"} ${x} ${y}`;
              })
              .join(" ")}
            fill="none"
            stroke="#f6c400"
            strokeLinecap="round"
            strokeWidth="3"
          />
        </svg>
      </div>

      <div className="mt-3 grid grid-cols-6 gap-y-2 text-center text-[13px] text-[#7b8394] md:grid-cols-9 lg:grid-cols-[repeat(18,minmax(0,1fr))]">
        {labels.map((label) => (
          <span key={label}>{label}</span>
        ))}
      </div>
    </div>
  );
}

function SmallCard({
  title,
  action,
  children,
}: {
  title: string;
  action?: string;
  children: React.ReactNode;
}) {
  return (
    <div className="rounded-2xl border border-[#e5e7eb] bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
      <div className="mb-4 flex items-center justify-between gap-3">
        <h3 className="text-[18px] font-semibold text-[#0b1220]">{title}</h3>
        {action ? (
          <span className="text-[13px] font-medium text-[#2554e8]">{action}</span>
        ) : null}
      </div>
      {children}
    </div>
  );
}

function StockBadge({ status }: { status: StockRow["status"] }) {
  return status === "Critical" ? (
    <span className="rounded-full border border-[#fecaca] bg-[#fef2f2] px-2.5 py-1 text-[12px] font-medium text-[#dc2626]">
      Critical
    </span>
  ) : (
    <span className="rounded-full border border-[#fde68a] bg-[#fffbeb] px-2.5 py-1 text-[12px] font-medium text-[#ca8a04]">
      Low
    </span>
  );
}

function UploadStatusBadge({ status }: { status: UploadRow["status"] }) {
  return status === "Published" ? (
    <span className="rounded-full border border-[#bbf7d0] bg-[#f0fdf4] px-2.5 py-1 text-[12px] font-medium text-[#16a34a]">
      Published
    </span>
  ) : (
    <span className="rounded-full border border-[#fde68a] bg-[#fffbeb] px-2.5 py-1 text-[12px] font-medium text-[#ca8a04]">
      Draft
    </span>
  );
}

function RevenueByCategory() {
  const categories = [
    { name: "TV Spare Parts", value: 68, amount: "UGX 12.5M" },
    { name: "Accessories", value: 48, amount: "UGX 8.1M" },
    { name: "Repair Tools", value: 36, amount: "UGX 5.4M" },
    { name: "Audio Parts", value: 24, amount: "UGX 3.2M" },
  ];

  return (
    <SmallCard title="Revenue by category" action="View report">
      <div className="space-y-4">
        {categories.map((item) => (
          <div key={item.name}>
            <div className="mb-1 flex items-center justify-between gap-3">
              <span className="text-[14px] font-medium text-[#111827]">{item.name}</span>
              <span className="text-[13px] text-[#6b7280]">{item.amount}</span>
            </div>
            <div className="h-2.5 rounded-full bg-[#eef2f7]">
              <div
                className="h-2.5 rounded-full bg-[#4f8cff]"
                style={{ width: `${item.value}%` }}
              />
            </div>
          </div>
        ))}
      </div>
    </SmallCard>
  );
}

function RecentUploadsTable({ products }: { products: UploadRow[] }) {
  return (
    <div className="overflow-hidden rounded-2xl border border-[#e5e7eb] bg-white shadow-[0_1px_2px_rgba(16,24,40,0.03)]">
      <div className="flex items-center justify-between gap-3 border-b border-[#eef1f4] px-5 py-4">
        <div>
          <h3 className="text-[18px] font-semibold text-[#0b1220]">Recent uploads</h3>
          <p className="text-[13px] text-[#7b8394]">
            Newly added products from your catalog
          </p>
        </div>
        <Link
          href="/admin/products"
          className="inline-flex items-center gap-2 rounded-xl border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] font-medium text-[#111827]"
        >
          <Eye size={16} />
          View all
        </Link>
      </div>

      <div className="overflow-x-auto">
        <table className="w-full min-w-[880px]">
          <thead>
            <tr className="bg-[#fbfbfc] text-left">
              <th className="px-5 py-3 text-[13px] font-medium text-[#6b7280]">Product</th>
              <th className="px-5 py-3 text-[13px] font-medium text-[#6b7280]">Category</th>
              <th className="px-5 py-3 text-[13px] font-medium text-[#6b7280]">Price</th>
              <th className="px-5 py-3 text-[13px] font-medium text-[#6b7280]">Status</th>
              <th className="px-5 py-3 text-[13px] font-medium text-[#6b7280]">Added</th>
              <th className="px-5 py-3 text-[13px] font-medium text-[#6b7280]"></th>
            </tr>
          </thead>
          <tbody>
            {products.length > 0 ? (
              products.map((row, index) => (
                <tr
                  key={row.id}
                  className={index !== products.length - 1 ? "border-t border-[#eef1f4]" : ""}
                >
                  <td className="px-5 py-4 text-[14px] font-semibold text-[#111827]">{row.name}</td>
                  <td className="px-5 py-4 text-[14px] text-[#111827]">{row.category}</td>
                  <td className="px-5 py-4 text-[14px] font-medium text-[#111827]">{row.price}</td>
                  <td className="px-5 py-4">
                    <UploadStatusBadge status={row.status} />
                  </td>
                  <td className="px-5 py-4 text-[14px] text-[#6b7280]">{row.createdAt}</td>
                  <td className="px-5 py-4 text-right">
                    <Link
                      href={`/admin/products/${row.id}`}
                      className="inline-flex items-center gap-2 text-[13px] font-medium text-[#2554e8] hover:underline"
                    >
                      Open
                    </Link>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={6} className="px-5 py-8 text-center text-[14px] text-[#7b8394]">
                  No uploaded products found yet.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export default async function EcommerceAdminDashboard() {
  const products = await getProducts();
  const publishedProducts = products.filter((product) => product.isPublished);
  const featuredProducts = products.filter((product) => product.isFeatured);
  const lowStock = products
    .filter((product) => product.stock > 0 && product.stock <= 5)
    .slice(0, 4)
    .map((product) => ({
      id: product.id,
      product: product.name,
      category: product.category,
      stock: product.stock,
      status: product.stock <= 2 ? "Critical" : "Low",
    } satisfies StockRow));
  const recentUploads = products.slice(0, 6).map((product) => ({
    id: product.id,
    name: product.name,
    category: product.category,
    price: formatUGX(product.price),
    status: product.isPublished ? "Published" : "Draft",
    createdAt: formatDashboardDate(new Date(product.createdAt)),
  } satisfies UploadRow));

  const statCards: StatCard[] = [
    {
      title: "Products",
      value: products.length.toLocaleString("en-US"),
      change: `${recentUploads.length} recent`,
      positive: true,
      headline: "Catalog count is live",
      subtext: "Updates immediately after upload",
    },
    {
      title: "Published",
      value: publishedProducts.length.toLocaleString("en-US"),
      change: `${products.length - publishedProducts.length} drafts`,
      positive: true,
      headline: "Storefront-ready products",
      subtext: "Visible to customers on the site",
    },
    {
      title: "Featured",
      value: featuredProducts.length.toLocaleString("en-US"),
      change: `${Math.max(products.length - featuredProducts.length, 0)} standard`,
      positive: true,
      headline: "Homepage-featured items",
      subtext: "Marked for highlighted placement",
    },
    {
      title: "Low Stock",
      value: lowStock.length.toLocaleString("en-US"),
      change: lowStock.some((product) => product.status === "Critical") ? "Needs attention" : "Stable",
      positive: !lowStock.some((product) => product.status === "Critical"),
      headline: lowStock.length > 0 ? "Restock soon" : "Inventory levels look good",
      subtext: "Tracks items with 5 units or fewer",
    },
  ];

  return (
    <>
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-4">
        {statCards.map((card) => (
          <StatCard key={card.title} card={card} />
        ))}
      </div>

      <div className="mt-6 grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,0.9fr)]">
        <SalesChart />

        <div className="grid gap-6">
          <RevenueByCategory />

          <SmallCard title="Customer activity" action="See all">
            <div className="space-y-4">
              {customerActivity.map((item) => (
                <div
                  key={`${item.customer}-${item.time}`}
                  className="flex items-start gap-3"
                >
                  <div className="mt-1 flex h-9 w-9 items-center justify-center rounded-full bg-[#eef3ff] text-[#2554e8]">
                    <Users size={16} />
                  </div>
                  <div className="min-w-0">
                    <p className="text-[14px] text-[#111827]">
                      <span className="font-semibold">{item.customer}</span>{" "}
                      <span className="text-[#4b5563]">{item.action}</span>
                    </p>
                    <p className="mt-1 text-[12px] text-[#9ca3af]">{item.time}</p>
                  </div>
                </div>
              ))}
            </div>
          </SmallCard>
        </div>
      </div>

      <div className="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(340px,0.9fr)]">
        <RecentUploadsTable products={recentUploads} />

        <div className="grid gap-6">
          <SmallCard title="Low stock products" action="Restock">
            <div className="space-y-4">
              {lowStock.length > 0 ? (
                lowStock.map((item) => (
                  <div
                    key={item.id}
                    className="flex items-center justify-between gap-3 rounded-2xl border border-[#eef1f4] p-3"
                  >
                    <div className="min-w-0">
                      <div className="truncate text-[14px] font-medium text-[#111827]">
                        {item.product}
                      </div>
                      <div className="mt-1 text-[12px] text-[#7b8394]">
                        Category: {item.category}
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="mb-1 text-[14px] font-semibold text-[#111827]">
                        {item.stock} left
                      </div>
                      <StockBadge status={item.status} />
                    </div>
                  </div>
                ))
              ) : (
                <div className="rounded-2xl border border-dashed border-[#d7dce3] px-4 py-6 text-center text-[14px] text-[#7b8394]">
                  No low-stock products right now.
                </div>
              )}
            </div>
          </SmallCard>

          <SmallCard title="Recent catalog status" action="Manage products">
            <div className="space-y-4">
              {recentUploads.length > 0 ? (
                recentUploads.map((item, index) => (
                  <div
                    key={item.id}
                    className="flex items-center justify-between gap-3 rounded-2xl border border-[#eef1f4] p-3"
                  >
                    <div className="flex min-w-0 items-center gap-3">
                      <div className="flex h-9 w-9 items-center justify-center rounded-full bg-[#111827] text-[14px] font-semibold text-white">
                        {index + 1}
                      </div>
                      <div className="min-w-0">
                        <div className="truncate text-[14px] font-medium text-[#111827]">
                          {item.name}
                        </div>
                        <div className="mt-1 text-[12px] text-[#7b8394]">
                          {item.category}
                        </div>
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="text-[14px] font-semibold text-[#111827]">
                        {item.price}
                      </div>
                      <div className="mt-1">
                        <UploadStatusBadge status={item.status} />
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="rounded-2xl border border-dashed border-[#d7dce3] px-4 py-6 text-center text-[14px] text-[#7b8394]">
                  Upload products to see them here.
                </div>
              )}
            </div>
          </SmallCard>
        </div>
      </div>

      <div className="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
        <SmallCard title="Pending shipments">
          <div className="flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff6ff] text-[#2563eb]">
              <Truck size={20} />
            </div>
            <div>
              <div className="text-[28px] font-semibold text-[#111827]">48</div>
              <div className="text-[13px] text-[#7b8394]">
                Orders waiting for dispatch
              </div>
            </div>
          </div>
        </SmallCard>

        <SmallCard title="Returns requested">
          <div className="flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff7ed] text-[#ea580c]">
              <RefreshCcw size={20} />
            </div>
            <div>
              <div className="text-[28px] font-semibold text-[#111827]">12</div>
              <div className="text-[13px] text-[#7b8394]">
                Awaiting review and approval
              </div>
            </div>
          </div>
        </SmallCard>

        <SmallCard title="Products to review">
          <div className="flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fef2f2] text-[#dc2626]">
              <AlertTriangle size={20} />
            </div>
            <div>
              <div className="text-[28px] font-semibold text-[#111827]">
                {products.filter((product) => !product.image || product.price <= 0).length}
              </div>
              <div className="text-[13px] text-[#7b8394]">
                Missing images or usable pricing
              </div>
            </div>
          </div>
        </SmallCard>
      </div>
    </>
  );
}
