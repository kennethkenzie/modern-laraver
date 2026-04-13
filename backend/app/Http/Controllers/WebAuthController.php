<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebAuthController extends Controller
{
    public function showLogin(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if (session('admin_token')) {
            return redirect('/dashboard');
        }

        return view('welcome');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $profile = Profile::where('email', strtolower(trim($request->email)))->first();

        if (!$profile || !Hash::check($request->password, $profile->password)) {
            return back()
                ->withErrors(['email' => 'Invalid email or password.'])
                ->withInput($request->only('email'));
        }

        if (!in_array($profile->role, ['admin', 'staff'])) {
            return back()
                ->withErrors(['email' => 'Access denied. An admin or staff account is required.'])
                ->withInput($request->only('email'));
        }

        return $this->issueDashboardSession($request, $profile);
    }

    public function register(Request $request)
    {
        $data = $request->validateWithBag('register', [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:profiles,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'in:admin,staff'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $profile = Profile::create([
            'id' => (string) Str::uuid(),
            'full_name' => trim($data['full_name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => isset($data['phone']) ? trim($data['phone']) : null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return $this->issueDashboardSession($request, $profile);
    }

    public function logout(Request $request)
    {
        $email = session('admin_profile.email');
        if ($email) {
            $profile = Profile::where('email', $email)->first();
            $profile?->tokens()->where('name', 'admin_dashboard')->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('web.login.page');
    }

    private function issueDashboardSession(Request $request, Profile $profile)
    {
        $profile->tokens()->where('name', 'admin_dashboard')->delete();
        $token = $profile->createToken('admin_dashboard')->plainTextToken;

        $request->session()->regenerate();

        session([
            'admin_token'   => $token,
            'admin_profile' => [
                'id'       => $profile->id,
                'fullName' => $profile->full_name,
                'email'    => $profile->email,
                'role'     => $profile->role,
                'avatar'   => $profile->avatar_url,
            ],
        ]);

        return redirect()->intended('/dashboard');
    }
}
