import Link from "next/link";
import NavBar from "@/components/NavBar";
import Footer from "@/components/Footer";

export default function ProfilePage() {
  return (
    <main className="min-h-screen bg-[#f8fafc]">
      <NavBar />

      <section className="mx-auto max-w-[1100px] px-4 py-8">
        <div className="mb-6 flex items-center justify-between gap-4">
          <div>
            <p className="text-sm uppercase tracking-[0.18em] text-[#0b63ce]">Profile</p>
            <h1 className="mt-2 text-3xl font-semibold text-[#111827]">Personal information</h1>
            <p className="mt-2 text-sm text-[#6b7280]">
              Keep your account details, delivery address, and communication preferences updated.
            </p>
          </div>
          <Link
            href="/user"
            className="rounded-xl border border-[#d1d5db] bg-white px-4 py-3 text-sm font-semibold text-[#111827]"
          >
            Back to account
          </Link>
        </div>

        <div className="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_360px]">
          <section className="rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm">
            <h2 className="text-xl font-semibold text-[#111827]">Edit details</h2>
            <div className="mt-6 grid gap-4 md:grid-cols-2">
              <ProfileField label="First name" value="Kenneth" />
              <ProfileField label="Last name" value="Nsubuga" />
              <ProfileField label="Email" value="kenneth@example.com" />
              <ProfileField label="Phone" value="+256 700 000 000" />
              <div className="md:col-span-2">
                <ProfileField label="Address" value="Plot 14, Kampala Road, Kampala" />
              </div>
              <ProfileField label="City" value="Kampala" />
              <ProfileField label="Country" value="Uganda" />
            </div>

            <div className="mt-6 flex flex-wrap gap-3">
              <button className="rounded-xl bg-[#111827] px-5 py-3 text-sm font-semibold text-white">
                Save changes
              </button>
              <button className="rounded-xl border border-[#d1d5db] bg-white px-5 py-3 text-sm font-semibold text-[#111827]">
                Cancel
              </button>
            </div>
          </section>

          <div className="space-y-6">
            <section className="rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm">
              <h2 className="text-xl font-semibold text-[#111827]">Account status</h2>
              <div className="mt-5 space-y-4 text-sm">
                <InfoRow label="Membership" value="Customer" />
                <InfoRow label="Email status" value="Verified" />
                <InfoRow label="Phone status" value="Pending confirmation" />
                <InfoRow label="Last update" value="Today" />
              </div>
            </section>

            <section className="rounded-3xl border border-[#e5e7eb] bg-white p-6 shadow-sm">
              <h2 className="text-xl font-semibold text-[#111827]">Quick actions</h2>
              <div className="mt-5 space-y-3">
                <QuickLink href="/user" label="Open account overview" />
                <QuickLink href="/cart" label="Review cart" />
                <QuickLink href="/" label="Continue shopping" />
              </div>
            </section>
          </div>
        </div>
      </section>

      <Footer />
    </main>
  );
}

function ProfileField({ label, value }: { label: string; value: string }) {
  return (
    <label className="block">
      <span className="mb-2 block text-sm font-semibold text-[#374151]">{label}</span>
      <input
        defaultValue={value}
        className="h-11 w-full rounded-xl border border-[#d1d5db] px-4 text-sm text-[#111827] outline-none focus:border-[#0b63ce]"
      />
    </label>
  );
}

function InfoRow({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex items-center justify-between gap-3 rounded-2xl bg-[#f8fafc] px-4 py-3">
      <span className="text-[#6b7280]">{label}</span>
      <span className="font-semibold text-[#111827]">{value}</span>
    </div>
  );
}

function QuickLink({ href, label }: { href: string; label: string }) {
  return (
    <Link
      href={href}
      className="block rounded-2xl border border-[#e5e7eb] px-4 py-3 text-sm font-medium text-[#111827] transition hover:bg-[#f8fafc]"
    >
      {label}
    </Link>
  );
}
