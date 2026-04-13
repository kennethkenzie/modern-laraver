<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Default store
        $store = Store::firstOrCreate(
            ['slug' => 'modern-electronics'],
            [
                'id'            => (string) Str::uuid(),
                'name'          => 'Modern Electronics Ltd',
                'support_email' => 'admin@e-modern.ug',
                'support_phone' => '+256700000000',
                'is_active'     => true,
            ]
        );

        // Default admin profile — update password if the record already exists
        $admin = Profile::firstOrCreate(
            ['email' => 'admin@e-modern.ug'],
            [
                'id'        => (string) Str::uuid(),
                'store_id'  => $store->id,
                'full_name' => 'Admin',
                'phone'     => '+256700000000',
                'password'  => Hash::make('123456Pp'),
                'role'      => 'admin',
            ]
        );

        // Ensure credentials are up to date even when the row already existed
        $admin->update([
            'store_id' => $store->id,
            'password' => Hash::make('123456Pp'),
            'role'     => 'admin',
        ]);
    }
}
