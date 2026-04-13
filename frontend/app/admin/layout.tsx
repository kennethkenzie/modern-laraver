"use client";

import "./admin-fonts.css";
import DashboardFooter from "./DashboardFooter";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useState } from "react";
import {
  BarChart3,
  Bell,
  Boxes,
  ChevronDown,
  ChevronRight,
  ClipboardList,
  CreditCard,
  DollarSign,
  FileText,
  LayoutDashboard,
  Menu,
  MessageSquare,
  Moon,
  MoreVertical,
  Package,
  Percent,
  Plus,
  RefreshCcw,
  Search,
  Settings,
  ShoppingCart,
  Star,
  Store,
  Tag,
  Truck,
  Users,
  Warehouse,
} from "lucide-react";

// ─── Types ────────────────────────────────────────────────────────────────────

type NavChild = {
  label: string;
  href: string;
};

type NavItem = {
  label: string;
  icon: React.ReactNode;
  href?: string;
  badge?: string;
  arrow?: boolean;
  children?: NavChild[];
};

// ─── Nav data (static — active state is derived from pathname) ───────────────

const COMMERCE_ITEMS: NavItem[] = [
  { label: "Overview",  icon: <LayoutDashboard size={18} />, href: "/admin" },
  { label: "Orders",    icon: <ShoppingCart size={18} />,    href: "/admin/orders" },
  {
    label: "Products",
    icon: <Package size={18} />,
    children: [
      { label: "All Products",   href: "/admin/products" },
      { label: "Add New Product",href: "/admin/products/add" },
      { label: "Brand",          href: "/admin/products/brand" },
      { label: "Categories",     href: "/admin/products/category" },
      { label: "Units",          href: "/admin/products/units" },
      { label: "Attribute Sets", href: "/admin/products/attributes" },
      { label: "Bulk Import",    href: "/admin/products/import" },
      { label: "Bulk Export",    href: "/admin/products/export" },
    ],
  },
  { label: "Customers", icon: <Users size={18} /> },
  { label: "Inventory", icon: <Warehouse size={18} /> },
  {
    label: "Shipping",
    icon: <Truck size={18} />,
    children: [
      { label: "Configuration",      href: "/admin/shipping" },
      { label: "Available Countries", href: "/admin/shipping" },
      { label: "Available States",    href: "/admin/shipping" },
      { label: "Available Cities",    href: "/admin/shipping" },
      { label: "Pickup Locations",    href: "/admin/payments/shipping-address" },
    ],
  },
  { label: "Returns", icon: <RefreshCcw size={18} /> },
];

const SALES_ITEMS: NavItem[] = [
  { label: "Revenue",      icon: <DollarSign size={18} /> },
  { label: "Discounts",    icon: <Percent size={18} /> },
  { label: "Coupons",      icon: <Tag size={18} /> },
  { label: "Transactions", icon: <CreditCard size={18} /> },
  { label: "Reports",      icon: <BarChart3 size={18} /> },
];

const STOREFRONT_ITEMS: NavItem[] = [
  {
    label: "StoreFront",
    icon: <Store size={18} />,
    children: [
      { label: "Header", href: "/admin/storefront/header" },
      { label: "Slider", href: "/admin/storefront/slider" },
    ],
  },
  { label: "Reviews",     icon: <Star size={18} /> },
  { label: "Messages",    icon: <MessageSquare size={18} />, badge: "12" },
  { label: "Pages",       icon: <FileText size={18} /> },
  { label: "Fulfillment", icon: <Boxes size={18} />, arrow: true },
];

const ADMIN_ITEMS: NavItem[] = [
  {
    label: "System Settings",
    icon: <Settings size={18} />,
    children: [
      { label: "General",            href: "/admin/settings" },
      { label: "Staff Accounts",     href: "/admin/settings/staff" },
      { label: "Roles & Permissions",href: "/admin/settings/permissions" },
      { label: "Activities Log",     href: "/admin/settings/logs" },
    ],
  },
  {
    label: "Payment Methods",
    icon: <CreditCard size={18} />,
    children: [
      { label: "Gateways",     href: "/admin/payments/gateways" },
      { label: "Bank Details", href: "/admin/payments/bank" },
    ],
  },
];

// ─── Helpers ──────────────────────────────────────────────────────────────────

/** Returns true when the current pathname matches or starts with the item's href. */
function isActive(pathname: string, href?: string): boolean {
  if (!href) return false;
  if (href === "/admin") return pathname === "/admin";
  return pathname === href || pathname.startsWith(href + "/");
}

/** Returns true when any child link is active. */
function hasActiveChild(pathname: string, children?: NavChild[]): boolean {
  return !!children?.some(c => isActive(pathname, c.href));
}

// ─── SidebarItem ──────────────────────────────────────────────────────────────

function SidebarItem({ item, pathname }: { item: NavItem; pathname: string }) {
  const active = isActive(pathname, item.href);
  const childActive = hasActiveChild(pathname, item.children);
  const hasChildren = !!item.children?.length;

  // Auto-open when a child page is active
  const [open, setOpen] = useState(() => childActive);

  const rowClass = [
    "flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left transition-all",
    active || childActive
      ? "bg-[#114f8f] text-white shadow-lg shadow-blue-900/20"
      : "text-gray-400 hover:bg-white/5 hover:text-white",
  ].join(" ");

  const inner = (
    <>
      <span className="flex items-center gap-3">
        <span className={active || childActive ? "text-white" : "text-gray-400"}>
          {item.icon}
        </span>
        <span className={active || childActive ? "font-semibold text-white" : "font-medium"}>
          {item.label}
        </span>
      </span>

      {hasChildren ? (
        <ChevronDown
          size={16}
          className={`transition-transform duration-200 ${open ? "rotate-180" : ""}`}
        />
      ) : item.badge ? (
        <span className="rounded-full bg-[#f6c400] px-2.5 py-1 text-[11px] font-black text-[#111827]">
          {item.badge}
        </span>
      ) : item.arrow ? (
        <ChevronRight size={16} className="text-gray-400" />
      ) : null}
    </>
  );

  return (
    <div className="w-full">
      {/* Leaf link with href and no children */}
      {!hasChildren && item.href ? (
        <Link href={item.href} className={rowClass}>
          {inner}
        </Link>
      ) : (
        <button
          onClick={() => hasChildren && setOpen(o => !o)}
          className={rowClass}
        >
          {inner}
        </button>
      )}

      {/* Children dropdown */}
      {hasChildren && open && (
        <div className="ml-9 mt-1 space-y-0.5 border-l border-gray-800 pl-4">
          {item.children!.map(child => {
            const childIsActive = isActive(pathname, child.href);
            return (
              <Link
                key={child.label}
                href={child.href}
                className={[
                  "block rounded-lg px-3 py-2 text-[14px] transition-all",
                  childIsActive
                    ? "bg-white/10 font-bold text-white"
                    : "font-medium text-gray-400 hover:bg-white/5 hover:text-white",
                ].join(" ")}
              >
                {child.label}
              </Link>
            );
          })}
        </div>
      )}
    </div>
  );
}

// ─── Layout ───────────────────────────────────────────────────────────────────

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();

  return (
    <div className="min-h-screen bg-[#f8fbff] text-[#111827] admin-dashboard">
      <div className="grid min-h-screen grid-cols-1 xl:grid-cols-[280px_minmax(0,1fr)]">

        {/* ── Sidebar ── */}
        <aside className="hidden border-r border-white/5 bg-[#111827] xl:flex xl:flex-col">

          {/* Logo */}
          <div className="flex items-center justify-between px-6 py-8">
            <div className="flex items-center gap-3">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#f6c400] text-[#111827] shadow-lg shadow-yellow-500/20">
                <ShoppingCart size={18} />
              </div>
              <div>
                <div className="text-[16px] font-black tracking-tight text-white uppercase">
                  Modern Electronics
                </div>
                <div className="text-[11px] font-bold text-gray-500 uppercase tracking-widest">
                  Control Center
                </div>
              </div>
            </div>
          </div>

          {/* Quick-action buttons */}
          <div className="px-4">
            <div className="flex gap-3">
              <Link
                href="/admin/products/add"
                className="flex h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-[#f6c400] px-4 text-[15px] font-bold text-[#111827] shadow-xl shadow-yellow-500/10 transition-all hover:bg-[#ffcf00] border border-yellow-300/20"
              >
                <Plus size={18} />
                Add Product
              </Link>
              <Link
                href="/admin/orders"
                className="flex h-12 w-12 items-center justify-center rounded-xl border border-white/10 bg-[#114f8f] text-white transition-colors hover:bg-[#0d3f74]"
              >
                <ClipboardList size={18} />
              </Link>
            </div>
          </div>

          {/* Nav sections */}
          <div className="mt-8 flex-1 overflow-y-auto px-4 pb-6 scrollbar-hide">

            <div>
              <p className="px-3 text-[11px] font-black uppercase tracking-[0.2em] text-gray-500">
                Ecommerce
              </p>
              <div className="mt-4 space-y-1">
                {COMMERCE_ITEMS.map(item => (
                  <SidebarItem key={item.label} item={item} pathname={pathname} />
                ))}
              </div>
            </div>

            <div className="mt-8">
              <p className="px-3 text-[11px] font-black uppercase tracking-[0.2em] text-gray-500">
                Sales & Marketing
              </p>
              <div className="mt-4 space-y-1">
                {SALES_ITEMS.map(item => (
                  <SidebarItem key={item.label} item={item} pathname={pathname} />
                ))}
              </div>
            </div>

            <div className="mt-8">
              <p className="px-3 text-[11px] font-black uppercase tracking-[0.2em] text-gray-500">
                Store Management
              </p>
              <div className="mt-4 space-y-1">
                {STOREFRONT_ITEMS.map(item => (
                  <SidebarItem key={item.label} item={item} pathname={pathname} />
                ))}
              </div>
            </div>

            <div className="mt-8">
              <p className="px-3 text-[11px] font-black uppercase tracking-[0.2em] text-gray-500">
                Administrative
              </p>
              <div className="mt-4 space-y-1">
                {ADMIN_ITEMS.map(item => (
                  <SidebarItem key={item.label} item={item} pathname={pathname} />
                ))}
              </div>
            </div>

          </div>

          {/* User footer */}
          <div className="border-t border-white/5 bg-black/20 px-6 py-5">
            <div className="flex items-center gap-3">
              <img
                src="https://i.pravatar.cc/80?img=12"
                alt="Store owner"
                className="h-11 w-11 rounded-full object-cover ring-2 ring-[#f6c400]"
              />
              <div className="min-w-0 flex-1">
                <div className="truncate text-[15px] font-bold text-white">Kenneth Store</div>
                <div className="truncate text-[12px] font-medium text-gray-500">Admin Account</div>
              </div>
              <button className="text-gray-500 hover:text-white transition-colors">
                <MoreVertical size={18} />
              </button>
            </div>
          </div>

        </aside>

        {/* ── Main content ── */}
        <main className="flex min-w-0 flex-col">

          {/* Topbar */}
          <header className="border-b border-gray-200 bg-white/80 px-4 py-3 backdrop-blur-md md:px-6 xl:px-8">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div className="flex min-w-0 items-center gap-4">
                <button className="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white text-[#111827] xl:hidden">
                  <Menu size={18} />
                </button>

                <div className="hidden items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-[14px] text-[#374151] md:flex font-bold">
                  <span className="text-gray-400">Store:</span>
                  <span className="text-[#114f8f]">Modern Electronics Ltd</span>
                  <ChevronDown size={14} className="text-gray-400" />
                </div>

                <div className="flex min-w-[220px] max-w-[520px] flex-1 items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 transition-all focus-within:border-[#114f8f] focus-within:ring-4 focus-within:ring-blue-50">
                  <Search size={18} className="text-gray-400" />
                  <input
                    type="text"
                    placeholder="Search Dashboard..."
                    className="w-full bg-transparent text-[15px] font-medium outline-none placeholder:text-gray-400"
                  />
                </div>
              </div>

              <div className="flex flex-wrap items-center gap-3">
                <Link
                  href="/"
                  target="_blank"
                  className="hidden rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-[14px] font-bold text-[#111827] hover:bg-gray-50 transition-all md:inline-flex items-center"
                >
                  View Store
                </Link>
                <Link
                  href="/admin/offers"
                  className="hidden rounded-xl bg-[#f6c400] px-5 py-2.5 text-[14px] font-black tracking-wide text-[#111827] md:inline-flex border border-yellow-200/20 hover:bg-[#ffcf00] transition-colors shadow-lg shadow-yellow-500/10"
                >
                  Create Offer
                </Link>
                <div className="h-8 w-px bg-gray-200 hidden md:block mx-1" />
                <button className="flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:text-[#111827] hover:border-gray-300 transition-all">
                  <Bell size={18} />
                </button>
                <button className="flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:text-[#111827] hover:border-gray-300 transition-all">
                  <Settings size={18} />
                </button>
                <button className="flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:text-[#111827] hover:border-gray-300 transition-all">
                  <Moon size={18} />
                </button>
                <div className="relative">
                  <img
                    src="https://i.pravatar.cc/80?img=32"
                    alt="User avatar"
                    className="h-11 w-11 rounded-xl object-cover border-2 border-white shadow-md"
                  />
                  <div className="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-2 border-white bg-green-500" />
                </div>
              </div>
            </div>
          </header>

          <div className="flex-1 px-4 py-8 md:px-6 xl:px-10 overflow-x-hidden">
            {children}
          </div>
          <DashboardFooter />

        </main>
      </div>
    </div>
  );
}
