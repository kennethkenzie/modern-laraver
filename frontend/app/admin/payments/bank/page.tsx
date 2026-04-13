"use client";

import { useState } from "react";
import { 
  Building2, 
  UserCircle2, 
  Hash, 
  MapPin, 
  Loader2, 
  Save, 
  CheckCircle2, 
  Plus,
  Trash2
} from "lucide-react";

type Account = {
  id: string;
  bankName: string;
  accountName: string;
  accountNumber: string;
  branch: string;
  swiftCode: string;
};

export default function BankDetailsPage() {
  const [isSaving, setIsSaving] = useState(false);
  const [showToast, setShowToast] = useState(false);

  const [accounts, setAccounts] = useState<Account[]>([
    {
      id: "acc001",
      bankName: "Stanbic Bank",
      accountName: "Modern Electronics Ltd",
      accountNumber: "9030012345678",
      branch: "Main Street Branch",
      swiftCode: "SBICUGKA",
    },
  ]);

  const addAccount = () => {
    setAccounts([...accounts, {
      id: crypto.randomUUID(),
      bankName: "",
      accountName: "",
      accountNumber: "",
      branch: "",
      swiftCode: "",
    }]);
  };

  const removeAccount = (id: string) => {
    setAccounts(prev => prev.filter(acc => acc.id !== id));
  };

  const updateAccount = (id: string, patch: Partial<Account>) => {
    setAccounts(prev => prev.map(acc => acc.id === id ? { ...acc, ...patch } : acc));
  };

  const handleSave = () => {
    setIsSaving(true);
    setTimeout(() => {
      setIsSaving(false);
      setShowToast(true);
      setTimeout(() => setShowToast(false), 3000);
    }, 1000);
  };

  return (
    <div className="bg-[#f8fbff] min-h-screen">
      <div className="mb-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div className="flex items-start gap-4">
          <span className="mt-2 h-2.5 w-10 rounded-full bg-[#f6c400] shadow-sm shadow-yellow-500/20" />
          <div>
            <h1 className="text-[32px] font-black tracking-tight text-gray-900 uppercase leading-none">Bank Details</h1>
            <p className="mt-2 text-sm font-bold text-gray-400 uppercase tracking-widest leading-none">Manage Direct Deposit Information</p>
          </div>
        </div>

        {showToast && (
          <div className="flex items-center gap-2 text-[13px] font-black text-emerald-600 bg-emerald-50 px-5 py-3 rounded-2xl border border-emerald-100 shadow-sm animate-in fade-in slide-in-from-top-4 uppercase tracking-wider">
            <CheckCircle2 size={18} />
            Accounts Saved Successfully
          </div>
        )}
      </div>

      <div className="space-y-6">
        {accounts.map((account, index) => (
          <section key={account.id} className="relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm transition-all hover:shadow-md">
            <div className="mb-8 flex items-center justify-between">
              <div className="flex items-center gap-4">
                <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-50 text-[#114f8f] border border-gray-100">
                    <Building2 size={24} />
                </div>
                <div>
                    <h3 className="text-xl font-black text-[#111827] uppercase">Account Placement #{index + 1}</h3>
                    <p className="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mt-1 underline decoration-[#f6c400] decoration-2 underline-offset-4">Direct Wire Configuration</p>
                </div>
              </div>
              
              {accounts.length > 1 && (
                <button 
                  onClick={() => removeAccount(account.id)}
                  className="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-100 transition-colors"
                >
                  <Trash2 size={18} />
                </button>
              )}
            </div>

            <div className="grid gap-6 md:grid-cols-2">
              <div className="space-y-2">
                <label className="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-none flex items-center gap-2 mb-2">
                    <Building2 size={12} className="text-[#114f8f]" />
                    Full Bank Name
                </label>
                <input 
                  value={account.bankName}
                  onChange={(e) => updateAccount(account.id, { bankName: e.target.value })}
                  placeholder="e.g. Stanbic Bank"
                  className="h-12 w-full rounded-2xl border border-gray-200 bg-gray-50/30 px-5 text-sm font-bold text-[#111827] outline-none hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50 transition-all"
                />
              </div>

              <div className="space-y-2">
                <label className="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-none flex items-center gap-2 mb-2">
                    <UserCircle2 size={12} className="text-[#114f8f]" />
                    Account Holder Name
                </label>
                <input 
                  value={account.accountName}
                  onChange={(e) => updateAccount(account.id, { accountName: e.target.value })}
                  placeholder="Official Company Name"
                  className="h-12 w-full rounded-2xl border border-gray-200 bg-gray-50/30 px-5 text-sm font-bold text-[#111827] outline-none hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50 transition-all"
                />
              </div>

              <div className="space-y-2">
                <label className="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-none flex items-center gap-2 mb-2">
                    <Hash size={12} className="text-[#114f8f]" strokeWidth={3} />
                    Account Number
                </label>
                <input 
                  value={account.accountNumber}
                  onChange={(e) => updateAccount(account.id, { accountNumber: e.target.value })}
                  placeholder="International Bank Account No"
                  className="h-12 w-full rounded-2xl border border-gray-200 bg-gray-50/30 px-5 text-sm font-bold text-[#111827] outline-none hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50 transition-all"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <label className="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-none flex items-center gap-2 mb-2">
                        <MapPin size={12} className="text-[#114f8f]" />
                        Branch Name
                    </label>
                    <input 
                      value={account.branch}
                      onChange={(e) => updateAccount(account.id, { branch: e.target.value })}
                      placeholder="City Branch"
                      className="h-12 w-full rounded-2xl border border-gray-200 bg-gray-50/30 px-5 text-sm font-bold text-[#111827] outline-none hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50 transition-all"
                    />
                  </div>
                  <div className="space-y-2">
                    <label className="text-[11px] font-black text-gray-400 uppercase tracking-widest leading-none flex items-center gap-2 mb-2">
                        <ShieldCheck size={12} className="text-[#114f8f]" />
                        SWIFT / BIC
                    </label>
                    <input 
                      value={account.swiftCode}
                      onChange={(e) => updateAccount(account.id, { swiftCode: e.target.value })}
                      placeholder="BIC Code"
                      className="h-12 w-full rounded-2xl border border-gray-200 bg-gray-50/30 px-5 text-sm font-bold text-[#111827] outline-none hover:border-gray-400 focus:border-[#114f8f] focus:ring-4 focus:ring-blue-50 transition-all"
                    />
                  </div>
              </div>
            </div>
            
            <div className="mt-8 flex items-center justify-end border-t border-gray-100 pt-6">
                <button
                  onClick={handleSave}
                  disabled={isSaving}
                  className="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-[#114f8f] px-8 text-[13px] font-black uppercase tracking-wide text-white transition-all hover:bg-[#0d3f74] disabled:opacity-50 shadow-lg shadow-blue-900/10"
                >
                  {isSaving ? <Loader2 size={16} className="animate-spin" /> : <Save size={16} />}
                  {isSaving ? "Saving Database..." : "Save Account Details"}
                </button>
            </div>
          </section>
        ))}

        <button 
          onClick={addAccount}
          className="flex w-full items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 px-8 py-5 text-sm font-black text-gray-400 transition-all hover:bg-gray-100 hover:border-gray-300 active:scale-[0.99] uppercase tracking-widest"
        >
          <Plus size={20} />
          Append New Bank Wire Channel
        </button>
      </div>
    </div>
  );
}

function ShieldCheck({ className, size }: { className?: string, size?: number }) {
    return (
        <svg 
            xmlns="http://www.w3.org/2000/svg" 
            width={size || 24} height={size || 24} 
            viewBox="0 0 24 24" 
            fill="none" 
            stroke="currentColor" 
            strokeWidth="2" 
            strokeLinecap="round" 
            strokeLinejoin="round" 
            className={className}
        >
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
            <path d="m9 12 2 2 4-4" />
        </svg>
    );
}
