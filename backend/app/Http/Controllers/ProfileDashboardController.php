<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileDashboardController extends Controller
{
    public function show(Request $request): View
    {
        return view('admin.account.profile', [
            'account' => $this->profileFromSession($request),
            'profile' => session('admin_profile'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $account = $this->profileFromSession($request);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('profiles', 'email')->ignore($account->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $account->update($data);
        $this->syncSession($account);

        return redirect()->route('dashboard.account.profile')->with('status', 'Profile updated successfully.');
    }

    public function settings(Request $request): View
    {
        return view('admin.account.settings', [
            'account' => $this->profileFromSession($request),
            'profile' => session('admin_profile'),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $account = $this->profileFromSession($request);

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $account->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ])->withInput();
        }

        $account->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('dashboard.account.settings')->with('status', 'Password updated successfully.');
    }

    private function profileFromSession(Request $request): Profile
    {
        $email = session('admin_profile.email');

        abort_unless($email, 403);

        return Profile::where('email', $email)->firstOrFail();
    }

    private function syncSession(Profile $profile): void
    {
        session([
            'admin_profile' => [
                'id' => $profile->id,
                'fullName' => $profile->full_name,
                'email' => $profile->email,
                'role' => $profile->role,
                'avatar' => $profile->avatar_url,
            ],
        ]);
    }
}
