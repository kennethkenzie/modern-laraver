"use client";

import {
    Search,
    Eye,
    ChevronLeft,
    ChevronRight,
    ChevronDown,
    Package,
    Truck,
    CheckCircle2,
    XCircle,
    Clock,
    RefreshCcw,
    ShoppingBag,
    DollarSign,
    TrendingUp,
    AlertCircle,
    X,
    MapPin,
    Phone,
    Mail,
    Calendar,
    CreditCard,
} from "lucide-react";
import { useState, useMemo } from "react";

type OrderStatus = "pending" | "processing" | "shipped" | "delivered" | "cancelled" | "refunded";
type PaymentStatus = "paid" | "unpaid" | "refunded" | "partial";

type OrderItem = {
    id: string;
    name: string;
    sku: string;
    qty: number;
    price: number;
};

type Order = {
    id: string;
    orderNumber: string;
    customer: {
        name: string;
        email: string;
        phone: string;
        avatar: string;
    };
    shippingAddress: {
        line1: string;
        city: string;
        country: string;
    };
    items: OrderItem[];
    total: number;
    paymentMethod: string;
    paymentStatus: PaymentStatus;
    status: OrderStatus;
    createdAt: string;
};

const MOCK_ORDERS: Order[] = [
    {
        id: "ord-001",
        orderNumber: "ME-10043",
        customer: { name: "James Okello", email: "james.okello@email.com", phone: "+256 700 123 456", avatar: "https://i.pravatar.cc/40?img=3" },
        shippingAddress: { line1: "Plot 12, Nakasero", city: "Kampala", country: "Uganda" },
        items: [
            { id: "p1", name: "Samsung T-CON Board 55\" UHD", sku: "TCON-SAM-55", qty: 2, price: 85000 },
            { id: "p2", name: "LG Power Supply Board", sku: "PSU-LG-43", qty: 1, price: 120000 },
        ],
        total: 290000,
        paymentMethod: "MTN MoMo",
        paymentStatus: "paid",
        status: "delivered",
        createdAt: "2026-04-10",
    },
    {
        id: "ord-002",
        orderNumber: "ME-10044",
        customer: { name: "Fatima Nakato", email: "fatima.nakato@email.com", phone: "+256 752 987 654", avatar: "https://i.pravatar.cc/40?img=5" },
        shippingAddress: { line1: "Bombo Road, Kawempe", city: "Kampala", country: "Uganda" },
        items: [
            { id: "p3", name: "Soldering Station 936A", sku: "SOLD-936A", qty: 1, price: 195000 },
        ],
        total: 195000,
        paymentMethod: "Cash on Delivery",
        paymentStatus: "unpaid",
        status: "shipped",
        createdAt: "2026-04-11",
    },
    {
        id: "ord-003",
        orderNumber: "ME-10045",
        customer: { name: "Robert Ssemwanga", email: "r.ssemwanga@gmail.com", phone: "+256 701 456 789", avatar: "https://i.pravatar.cc/40?img=7" },
        shippingAddress: { line1: "Lugogo Bypass, Nakawa", city: "Kampala", country: "Uganda" },
        items: [
            { id: "p4", name: "HDMI Cable 2.0 (2m)", sku: "HDMI-2M-BLK", qty: 5, price: 18000 },
            { id: "p5", name: "TV Remote Universal", sku: "REM-UNI-V3", qty: 3, price: 25000 },
            { id: "p6", name: "LED Backlight Strip 32\"", sku: "LED-32-STR", qty: 2, price: 55000 },
        ],
        total: 275000,
        paymentMethod: "Airtel Money",
        paymentStatus: "paid",
        status: "processing",
        createdAt: "2026-04-12",
    },
    {
        id: "ord-004",
        orderNumber: "ME-10046",
        customer: { name: "Grace Achieng", email: "g.achieng@outlook.com", phone: "+256 785 321 012", avatar: "https://i.pravatar.cc/40?img=9" },
        shippingAddress: { line1: "Kampala Road, CBD", city: "Kampala", country: "Uganda" },
        items: [
            { id: "p7", name: "Main Board Samsung 43\" Smart", sku: "MAIN-SAM-43S", qty: 1, price: 310000 },
        ],
        total: 310000,
        paymentMethod: "Stripe",
        paymentStatus: "paid",
        status: "pending",
        createdAt: "2026-04-12",
    },
    {
        id: "ord-005",
        orderNumber: "ME-10047",
        customer: { name: "Patrick Mugisha", email: "p.mugisha@icloud.com", phone: "+256 712 654 321", avatar: "https://i.pravatar.cc/40?img=11" },
        shippingAddress: { line1: "Entebbe Road, Makindye", city: "Kampala", country: "Uganda" },
        items: [
            { id: "p8", name: "Digital Multimeter Pro", sku: "MULT-PRO-DX", qty: 2, price: 145000 },
        ],
        total: 290000,
        paymentMethod: "Flutterwave",
        paymentStatus: "paid",
        status: "cancelled",
        createdAt: "2026-04-09",
    },
    {
        id: "ord-006",
        orderNumber: "ME-10048",
        customer: { name: "Aisha Namubiru", email: "aisha.namu@email.com", phone: "+256 770 888 123", avatar: "https://i.pravatar.cc/40?img=16" },
        shippingAddress: { line1: "Ntinda Complex", city: "Kampala", country: "Uganda" },
        items: [
            { id: "p9", name: "Fridge Thermostat NTC", sku: "FRIDGE-THERM-01", qty: 3, price: 42000 },
            { id: "p10", name: "Microwave Magnetron 900W", sku: "MWV-MAG-900", qty: 1, price: 185000 },
        ],
        total: 311000,
        paymentMethod: "MTN MoMo",
        paymentStatus: "refunded",
        status: "refunded",
        createdAt: "2026-04-08",
    },
    {
        id: "ord-007",
        orderNumber: "ME-10049",
        customer: { name: "David Tumusiime", email: "d.tumusiime@gmail.com", phone: "+256 702 111 234", avatar: "https://i.pravatar.cc/40?img=20" },
        shippingAddress: { line1: "Wandegeya Market St", city: "Kampala", country: "Uganda" },
        items: [
            { id: "p11", name: "Wall Mount Bracket 55-75\"", sku: "WM-7575-BLK", qty: 1, price: 78000 },
        ],
        total: 78000,
        paymentMethod: "Cash on Delivery",
        paymentStatus: "unpaid",
        status: "pending",
        createdAt: "2026-04-13",
    },
    {
        id: "ord-008",
        orderNumber: "ME-10050",
        customer: { name: "Christine Akello", email: "c.akello@yahoo.com", phone: "+256 756 444 789", avatar: "https://i.pravatar.cc/40?img=25" },
        shippingAddress: { line1: "Muyenga Hill", city: "Kampala", country: "Uganda" },
        items: [
            { id: "p12", name: "Power Adapter 12V 3A", sku: "PWR-12V-3A", qty: 4, price: 32000 },
            { id: "p13", name: "AV Cable RCA 1.5m", sku: "AV-RCA-1.5", qty: 2, price: 15000 },
        ],
        total: 158000,
        paymentMethod: "Airtel Money",
        paymentStatus: "paid",
        status: "shipped",
        createdAt: "2026-04-11",
    },
];

const STATUS_CONFIG: Record<OrderStatus, { label: string; color: string; bg: string; icon: React.ReactNode }> = {
    pending:    { label: "Pending",    color: "text-amber-700",  bg: "bg-amber-50 border-amber-200",   icon: <Clock size={12} /> },
    processing: { label: "Processing", color: "text-blue-700",   bg: "bg-blue-50 border-blue-200",     icon: <RefreshCcw size={12} /> },
    shipped:    { label: "Shipped",    color: "text-indigo-700", bg: "bg-indigo-50 border-indigo-200", icon: <Truck size={12} /> },
    delivered:  { label: "Delivered",  color: "text-green-700",  bg: "bg-green-50 border-green-200",   icon: <CheckCircle2 size={12} /> },
    cancelled:  { label: "Cancelled",  color: "text-red-700",    bg: "bg-red-50 border-red-200",       icon: <XCircle size={12} /> },
    refunded:   { label: "Refunded",   color: "text-purple-700", bg: "bg-purple-50 border-purple-200", icon: <RefreshCcw size={12} /> },
};

const PAYMENT_CONFIG: Record<PaymentStatus, { label: string; color: string }> = {
    paid:     { label: "Paid",     color: "text-green-600" },
    unpaid:   { label: "Unpaid",   color: "text-red-500" },
    refunded: { label: "Refunded", color: "text-purple-600" },
    partial:  { label: "Partial",  color: "text-amber-600" },
};

const STATUS_TABS: { key: "all" | OrderStatus; label: string }[] = [
    { key: "all",        label: "All Orders" },
    { key: "pending",    label: "Pending" },
    { key: "processing", label: "Processing" },
    { key: "shipped",    label: "Shipped" },
    { key: "delivered",  label: "Delivered" },
    { key: "cancelled",  label: "Cancelled" },
    { key: "refunded",   label: "Refunded" },
];

function formatCurrency(amount: number) {
    return `UGX ${amount.toLocaleString()}`;
}

function StatusBadge({ status }: { status: OrderStatus }) {
    const cfg = STATUS_CONFIG[status];
    return (
        <span className={`inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-black uppercase tracking-widest ${cfg.bg} ${cfg.color}`}>
            {cfg.icon}
            {cfg.label}
        </span>
    );
}

function StatCard({ icon, label, value, sub, color }: { icon: React.ReactNode; label: string; value: string; sub?: string; color: string }) {
    return (
        <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div className={`mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl ${color}`}>
                {icon}
            </div>
            <div className="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">{label}</div>
            <div className="mt-1 text-[28px] font-black tracking-tight text-[#111827]">{value}</div>
            {sub && <div className="mt-0.5 text-[12px] font-bold text-gray-400">{sub}</div>}
        </div>
    );
}

export default function OrdersPage() {
    const [activeTab, setActiveTab] = useState<"all" | OrderStatus>("all");
    const [searchQuery, setSearchQuery] = useState("");
    const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
    const [orders, setOrders] = useState<Order[]>(MOCK_ORDERS);
    const [currentPage, setCurrentPage] = useState(1);
    const PER_PAGE = 6;

    const filtered = useMemo(() => {
        return orders.filter((o) => {
            const matchesTab = activeTab === "all" || o.status === activeTab;
            const q = searchQuery.toLowerCase();
            const matchesSearch =
                !q ||
                o.orderNumber.toLowerCase().includes(q) ||
                o.customer.name.toLowerCase().includes(q) ||
                o.customer.email.toLowerCase().includes(q);
            return matchesTab && matchesSearch;
        });
    }, [orders, activeTab, searchQuery]);

    const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
    const paginated = filtered.slice((currentPage - 1) * PER_PAGE, currentPage * PER_PAGE);

    const stats = useMemo(() => {
        const total = orders.length;
        const pending = orders.filter(o => o.status === "pending").length;
        const delivered = orders.filter(o => o.status === "delivered").length;
        const revenue = orders.filter(o => o.paymentStatus === "paid").reduce((s, o) => s + o.total, 0);
        return { total, pending, delivered, revenue };
    }, [orders]);

    const updateStatus = (orderId: string, newStatus: OrderStatus) => {
        setOrders(prev => prev.map(o => o.id === orderId ? { ...o, status: newStatus } : o));
        if (selectedOrder?.id === orderId) {
            setSelectedOrder(prev => prev ? { ...prev, status: newStatus } : prev);
        }
    };

    return (
        <div className="space-y-8">
            {/* Header */}
            <div className="flex items-start gap-4">
                <span className="mt-2 h-2.5 w-10 rounded-full bg-[#f6c400] shadow-sm shadow-yellow-500/20" />
                <div>
                    <h1 className="text-[32px] font-black tracking-tight text-[#111827] uppercase leading-none">
                        Orders Management
                    </h1>
                    <p className="mt-2 text-sm font-bold text-gray-400 uppercase tracking-[0.15em]">
                        {orders.length} total orders on record
                    </p>
                </div>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <StatCard
                    icon={<ShoppingBag size={22} className="text-[#114f8f]" />}
                    label="Total Orders"
                    value={String(stats.total)}
                    sub="all time"
                    color="bg-blue-50"
                />
                <StatCard
                    icon={<Clock size={22} className="text-amber-600" />}
                    label="Pending"
                    value={String(stats.pending)}
                    sub="awaiting action"
                    color="bg-amber-50"
                />
                <StatCard
                    icon={<CheckCircle2 size={22} className="text-green-600" />}
                    label="Delivered"
                    value={String(stats.delivered)}
                    sub="fulfilled"
                    color="bg-green-50"
                />
                <StatCard
                    icon={<DollarSign size={22} className="text-purple-600" />}
                    label="Revenue"
                    value={`UGX ${(stats.revenue / 1000).toFixed(0)}K`}
                    sub="paid orders"
                    color="bg-purple-50"
                />
            </div>

            {/* Table Card */}
            <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                {/* Filters bar */}
                <div className="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 md:flex-row md:items-center md:justify-between">
                    <div className="flex flex-wrap gap-1.5">
                        {STATUS_TABS.map(tab => {
                            const count = tab.key === "all" ? orders.length : orders.filter(o => o.status === tab.key).length;
                            return (
                                <button
                                    key={tab.key}
                                    onClick={() => { setActiveTab(tab.key); setCurrentPage(1); }}
                                    className={[
                                        "inline-flex h-8 items-center gap-2 rounded-lg px-3 text-[12px] font-black uppercase tracking-widest transition-all",
                                        activeTab === tab.key
                                            ? "bg-[#111827] text-white shadow-sm"
                                            : "bg-gray-100 text-gray-500 hover:bg-gray-200",
                                    ].join(" ")}
                                >
                                    {tab.label}
                                    <span className={`rounded-full px-1.5 py-0.5 text-[10px] font-black ${activeTab === tab.key ? "bg-white/20 text-white" : "bg-white text-gray-600"}`}>
                                        {count}
                                    </span>
                                </button>
                            );
                        })}
                    </div>

                    <div className="flex min-w-[260px] max-w-[380px] items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5">
                        <Search size={16} className="text-gray-400" />
                        <input
                            type="text"
                            placeholder="Search by order # or customer..."
                            value={searchQuery}
                            onChange={e => { setSearchQuery(e.target.value); setCurrentPage(1); }}
                            className="w-full bg-transparent text-sm font-medium outline-none placeholder:text-gray-400"
                        />
                        {searchQuery && (
                            <button onClick={() => setSearchQuery("")} className="text-gray-400 hover:text-gray-600">
                                <X size={14} />
                            </button>
                        )}
                    </div>
                </div>

                {/* Table */}
                <div className="overflow-x-auto">
                    <table className="min-w-full text-sm">
                        <thead>
                            <tr className="border-b border-gray-100 bg-gray-50/50 text-left text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">
                                <th className="px-6 py-4">Order</th>
                                <th className="px-6 py-4">Customer</th>
                                <th className="px-6 py-4">Date</th>
                                <th className="px-6 py-4">Items</th>
                                <th className="px-6 py-4">Total</th>
                                <th className="px-6 py-4">Payment</th>
                                <th className="px-6 py-4">Status</th>
                                <th className="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-50">
                            {paginated.length === 0 ? (
                                <tr>
                                    <td colSpan={8} className="px-6 py-16 text-center">
                                        <div className="flex flex-col items-center gap-3 text-gray-400">
                                            <Package size={36} strokeWidth={1.5} />
                                            <p className="text-sm font-bold">
                                                {searchQuery ? `No orders matching "${searchQuery}"` : "No orders in this category"}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            ) : (
                                paginated.map(order => (
                                    <tr key={order.id} className="group transition-colors hover:bg-gray-50/40">
                                        <td className="px-6 py-4">
                                            <span className="font-black text-[#114f8f] tracking-tight">#{order.orderNumber}</span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <img
                                                    src={order.customer.avatar}
                                                    alt={order.customer.name}
                                                    className="h-8 w-8 rounded-full object-cover border border-gray-100"
                                                />
                                                <div>
                                                    <div className="font-bold text-gray-900 whitespace-nowrap">{order.customer.name}</div>
                                                    <div className="text-[11px] font-medium text-gray-400">{order.customer.email}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-gray-500 font-medium whitespace-nowrap">{order.createdAt}</td>
                                        <td className="px-6 py-4">
                                            <span className="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-gray-100 px-2 text-[11px] font-black text-gray-600">
                                                {order.items.reduce((s, i) => s + i.qty, 0)}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 font-black text-gray-900 whitespace-nowrap">{formatCurrency(order.total)}</td>
                                        <td className="px-6 py-4">
                                            <div>
                                                <span className={`text-[12px] font-black uppercase tracking-widest ${PAYMENT_CONFIG[order.paymentStatus].color}`}>
                                                    {PAYMENT_CONFIG[order.paymentStatus].label}
                                                </span>
                                                <div className="text-[11px] font-medium text-gray-400">{order.paymentMethod}</div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <StatusBadge status={order.status} />
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <button
                                                onClick={() => setSelectedOrder(order)}
                                                className="inline-flex h-8 items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 text-[12px] font-black uppercase tracking-widest text-gray-600 transition-all hover:border-[#114f8f] hover:text-[#114f8f] shadow-sm active:scale-95"
                                            >
                                                <Eye size={13} />
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                <div className="flex items-center justify-between border-t border-gray-100 px-6 py-4">
                    <span className="text-[12px] font-bold text-gray-400">
                        Showing {Math.min((currentPage - 1) * PER_PAGE + 1, filtered.length)}–{Math.min(currentPage * PER_PAGE, filtered.length)} of {filtered.length} orders
                    </span>
                    <div className="flex items-center gap-1.5">
                        <button
                            onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
                            disabled={currentPage === 1}
                            className="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition hover:bg-gray-50 disabled:opacity-40"
                        >
                            <ChevronLeft size={16} />
                        </button>
                        {Array.from({ length: totalPages }, (_, i) => i + 1).map(p => (
                            <button
                                key={p}
                                onClick={() => setCurrentPage(p)}
                                className={`flex h-9 min-w-9 items-center justify-center rounded-xl border px-3 text-[13px] font-black transition ${currentPage === p ? "border-[#114f8f] bg-[#114f8f] text-white" : "border-gray-200 bg-white text-gray-500 hover:bg-gray-50"}`}
                            >
                                {p}
                            </button>
                        ))}
                        <button
                            onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
                            disabled={currentPage === totalPages}
                            className="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-400 transition hover:bg-gray-50 disabled:opacity-40"
                        >
                            <ChevronRight size={16} />
                        </button>
                    </div>
                </div>
            </div>

            {/* Order Detail Drawer/Modal */}
            {selectedOrder && (
                <div className="fixed inset-0 z-50 flex items-start justify-end bg-black/40 backdrop-blur-sm" onClick={() => setSelectedOrder(null)}>
                    <div
                        className="relative h-full w-full max-w-[560px] overflow-y-auto bg-white shadow-2xl"
                        onClick={e => e.stopPropagation()}
                    >
                        {/* Drawer Header */}
                        <div className="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-5">
                            <div>
                                <p className="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Order Details</p>
                                <h2 className="text-xl font-black text-[#111827]">#{selectedOrder.orderNumber}</h2>
                            </div>
                            <button
                                onClick={() => setSelectedOrder(null)}
                                className="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:text-gray-900 transition-colors"
                            >
                                <X size={18} />
                            </button>
                        </div>

                        <div className="space-y-6 p-6">
                            {/* Status Update */}
                            <div className="rounded-2xl border border-gray-200 p-5">
                                <p className="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Update Status</p>
                                <div className="flex flex-wrap gap-2">
                                    {(["pending", "processing", "shipped", "delivered", "cancelled", "refunded"] as OrderStatus[]).map(s => {
                                        const cfg = STATUS_CONFIG[s];
                                        return (
                                            <button
                                                key={s}
                                                onClick={() => updateStatus(selectedOrder.id, s)}
                                                className={[
                                                    "inline-flex h-8 items-center gap-1.5 rounded-lg border px-3 text-[11px] font-black uppercase tracking-widest transition-all",
                                                    selectedOrder.status === s
                                                        ? `${cfg.bg} ${cfg.color} shadow-sm`
                                                        : "border-gray-200 bg-white text-gray-500 hover:bg-gray-50",
                                                ].join(" ")}
                                            >
                                                {cfg.icon}
                                                {cfg.label}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Customer Info */}
                            <div className="rounded-2xl border border-gray-200 p-5 space-y-3">
                                <p className="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Customer</p>
                                <div className="flex items-center gap-3">
                                    <img src={selectedOrder.customer.avatar} alt="" className="h-12 w-12 rounded-full object-cover border border-gray-100" />
                                    <div>
                                        <div className="font-black text-gray-900">{selectedOrder.customer.name}</div>
                                        <div className="text-sm font-medium text-gray-500">{selectedOrder.customer.email}</div>
                                    </div>
                                </div>
                                <div className="space-y-2 pt-1">
                                    <div className="flex items-center gap-2 text-sm text-gray-600">
                                        <Phone size={14} className="text-gray-400" />
                                        <span className="font-medium">{selectedOrder.customer.phone}</span>
                                    </div>
                                    <div className="flex items-center gap-2 text-sm text-gray-600">
                                        <Mail size={14} className="text-gray-400" />
                                        <span className="font-medium">{selectedOrder.customer.email}</span>
                                    </div>
                                    <div className="flex items-center gap-2 text-sm text-gray-600">
                                        <MapPin size={14} className="text-gray-400" />
                                        <span className="font-medium">{selectedOrder.shippingAddress.line1}, {selectedOrder.shippingAddress.city}, {selectedOrder.shippingAddress.country}</span>
                                    </div>
                                    <div className="flex items-center gap-2 text-sm text-gray-600">
                                        <Calendar size={14} className="text-gray-400" />
                                        <span className="font-medium">{selectedOrder.createdAt}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Order Items */}
                            <div className="rounded-2xl border border-gray-200 overflow-hidden">
                                <div className="border-b border-gray-100 bg-gray-50/50 px-5 py-3">
                                    <p className="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">
                                        Items ({selectedOrder.items.reduce((s, i) => s + i.qty, 0)})
                                    </p>
                                </div>
                                <div className="divide-y divide-gray-50">
                                    {selectedOrder.items.map(item => (
                                        <div key={item.id} className="flex items-center justify-between px-5 py-3.5">
                                            <div className="min-w-0">
                                                <div className="font-bold text-gray-900 truncate text-sm">{item.name}</div>
                                                <div className="text-[11px] font-black uppercase tracking-wider text-gray-400">{item.sku} &bull; Qty: {item.qty}</div>
                                            </div>
                                            <div className="ml-4 shrink-0 text-right">
                                                <div className="font-black text-gray-900 text-sm">{formatCurrency(item.price * item.qty)}</div>
                                                <div className="text-[11px] text-gray-400">{formatCurrency(item.price)} each</div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <div className="border-t border-gray-200 bg-gray-50/50 px-5 py-4">
                                    <div className="flex justify-between">
                                        <span className="text-[13px] font-black uppercase tracking-widest text-gray-500">Order Total</span>
                                        <span className="text-[18px] font-black text-[#111827]">{formatCurrency(selectedOrder.total)}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Payment Info */}
                            <div className="rounded-2xl border border-gray-200 p-5 space-y-3">
                                <p className="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400">Payment</p>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <CreditCard size={16} className="text-gray-400" />
                                        <span className="font-bold text-gray-900">{selectedOrder.paymentMethod}</span>
                                    </div>
                                    <span className={`text-[12px] font-black uppercase tracking-widest ${PAYMENT_CONFIG[selectedOrder.paymentStatus].color}`}>
                                        {PAYMENT_CONFIG[selectedOrder.paymentStatus].label}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
