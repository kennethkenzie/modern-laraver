<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     * Register a new customer profile.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'required|string|max:50',
            'email'     => 'nullable|email|max:255|unique:profiles,email',
            'password'  => 'required|string|min:6',
        ]);

        $phone = $this->normalizePhone($data['phone']);

        if (Profile::where('phone', $phone)->exists()) {
            return response()->json(['error' => 'An account with that phone number already exists.'], 422);
        }

        $profile = Profile::create([
            'id'        => (string) Str::uuid(),
            'full_name' => trim($data['full_name']),
            'phone'     => $phone,
            'email'     => isset($data['email']) ? strtolower(trim($data['email'])) : null,
            'password'  => Hash::make($data['password']),
            'role'      => 'customer',
        ]);

        $token = $profile->createToken('auth_token')->plainTextToken;

        return response()->json([
            'ok'      => true,
            'token'   => $token,
            'profile' => $this->formatProfile($profile),
        ]);
    }

    /**
     * POST /api/auth/login
     * Login with email + password (phone still accepted as fallback).
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required_without:phone|nullable|email',
            'phone'    => 'required_without:email|nullable|string',
            'password' => 'required|string',
        ]);

        // Resolve the profile by email first, then phone
        if (! empty($data['email'])) {
            $profile = Profile::where('email', strtolower(trim($data['email'])))->first();
        } else {
            $phone   = $this->normalizePhone($data['phone']);
            $profile = Profile::where('phone', $phone)->first();
        }

        if (! $profile || ! Hash::check($data['password'], $profile->password)) {
            return response()->json(['error' => 'Invalid email or password.'], 401);
        }

        $token = $profile->createToken('auth_token')->plainTextToken;

        return response()->json([
            'ok'      => true,
            'token'   => $token,
            'profile' => $this->formatProfile($profile),
        ]);
    }

    /**
     * POST /api/auth/logout
     * Revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user('sanctum')?->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * GET /api/auth/me
     * Return the authenticated profile.
     */
    public function me(Request $request): JsonResponse
    {
        $profile = $request->user('sanctum');

        if (! $profile) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return response()->json(['profile' => $this->formatProfile($profile)]);
    }

    /**
     * PATCH /api/auth/me
     * Update profile details.
     */
    public function updateMe(Request $request): JsonResponse
    {
        $profile = $request->user('sanctum');

        if (! $profile) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email'     => 'sometimes|nullable|email|max:255',
            'phone'     => 'sometimes|string|max:50',
            'avatar_url'=> 'sometimes|nullable|string',
        ]);

        if (isset($data['phone'])) {
            $data['phone'] = $this->normalizePhone($data['phone']);
        }

        $profile->update($data);

        return response()->json(['ok' => true, 'profile' => $this->formatProfile($profile->fresh())]);
    }

    /**
     * POST /api/auth/send-otp
     * Send a phone OTP via Twilio Verify.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $data  = $request->validate(['phone' => 'required|string']);
        $phone = trim($data['phone']);

        $sid    = config('services.twilio.sid');
        $token  = config('services.twilio.token');
        $verifySid = config('services.twilio.verify_sid');

        if (! $sid || ! $token || ! $verifySid) {
            return response()->json(['error' => 'Twilio is not configured.'], 500);
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://verify.twilio.com/v2/Services/{$verifySid}/Verifications", [
                'To'      => $phone,
                'Channel' => 'sms',
            ]);

        if ($response->failed()) {
            $body = $response->json();
            $code = $body['code'] ?? null;

            if ($code === 60200) {
                return response()->json([
                    'error' => 'Invalid phone number format. Use the country code, for example +256700000000.',
                ], 400);
            }

            return response()->json(['error' => $body['message'] ?? 'Failed to send OTP.'], 400);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * POST /api/auth/verify-otp
     * Verify phone OTP and sign in (or register) the user.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'code'  => 'required|string',
        ]);

        $phone = trim($data['phone']);
        $code  = trim($data['code']);

        $sid       = config('services.twilio.sid');
        $token     = config('services.twilio.token');
        $verifySid = config('services.twilio.verify_sid');

        if (! $sid || ! $token || ! $verifySid) {
            return response()->json(['error' => 'Twilio is not configured.'], 500);
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://verify.twilio.com/v2/Services/{$verifySid}/VerificationCheck", [
                'To'   => $phone,
                'Code' => $code,
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Invalid or expired OTP code.'], 400);
        }

        $body = $response->json();

        if (($body['status'] ?? '') !== 'approved') {
            return response()->json(['error' => 'Invalid or expired OTP code.'], 400);
        }

        return response()->json(['ok' => true]);
    }

    // ─── helpers ──────────────────────────────────────────────

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);

        // Convert local Uganda numbers (07xx / 03xx) to +256 format
        if (preg_match('/^0([37]\d{8})$/', $phone, $m)) {
            return '+256' . $m[1];
        }

        // Already has a +
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        return $phone;
    }

    private function formatProfile(Profile $profile): array
    {
        return [
            'id'         => $profile->id,
            'fullName'   => $profile->full_name,
            'email'      => $profile->email,
            'phone'      => $profile->phone,
            'role'       => $profile->role,
            'avatarUrl'  => $profile->avatar_url,
        ];
    }
}
