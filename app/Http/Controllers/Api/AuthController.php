<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{User, Tenant};
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, DB, Storage};

class AuthController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    // ─────────────────────────────────────────────────
    // POST /api/auth/register
    // Admin self registration
    // ─────────────────────────────────────────────────
    public function register(Request $request)
    {
        try {
            $request->validate([
                'vendor_name'           => 'required|string|max:255',
                'email'                 => 'required|email|unique:users,email',
                'phone'                 => 'required|string|max:15',
                'password'              => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string',
                'gstin'                 => 'nullable|string|max:15',
                'address'               => 'nullable|string|max:500',
                'logo'                  => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            ], [
                'vendor_name.required'           => 'Vendor name is required.',
                'email.required'                 => 'Email address is required.',
                'email.unique'                   => 'This email is already registered.',
                'phone.required'                 => 'Phone number is required.',
                'password.required'              => 'Password is required.',
                'password.min'                   => 'Password must be at least 8 characters.',
                'password.confirmed'             => 'Password and confirm password do not match.',
                'password_confirmation.required' => 'Please confirm your password.',
                'logo.mimes'                     => 'Logo must be JPG or PNG.',
                'logo.max'                       => 'Logo size must not exceed 2MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {

                // Logo upload
                $logoPath = null;
                if ($request->hasFile('logo')) {
                    $file     = $request->file('logo');
                    $fileName = 'logo-' . now()->format('YmdHis') . '.' . $file->extension();
                    $stored   = $file->storeAs('public/tenants/logos', $fileName);
                    $logoPath = str_replace('public/', '', $stored);
                }

                // 1. Create Tenant
                $tenant = Tenant::create([
                    'company_name' => $request->vendor_name,
                    'email'        => $request->email,
                    'phone'        => $request->phone,
                    'gstin'        => $request->gstin   ?? null,
                    'address'      => $request->address ?? null,
                    'logo_path'    => $logoPath,
                    'is_active'    => true,
                ]);

                // 2. Create Admin User
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name'      => $request->vendor_name,
                    'email'     => $request->email,
                    'phone'     => $request->phone,
                    'password'  => Hash::make($request->password),
                    'role'      => 'admin',
                    'is_active' => true,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful. You can now login.',
                    'data'    => [
                        'user' => [
                            'id'           => $user->id,
                            'name'         => $user->name,
                            'email'        => $user->email,
                            'phone'        => $user->phone,
                            'role'         => $user->role,
                            'tenant_id'    => $tenant->id,
                            'company_name' => $tenant->company_name,
                            'gstin'        => $tenant->gstin,
                            'address'      => $tenant->address,
                            'logo_url'     => $tenant->logo_url,
                        ],
                    ],
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/auth/profile/update
    // Edit profile — sirf jo field bhejo wahi update
    // ─────────────────────────────────────────────────
    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'vendor_name' => 'nullable|string|max:255',
                'phone'       => 'nullable|string|max:15',
                'gstin'       => 'nullable|string|max:15',
                'address'     => 'nullable|string|max:500',
                'logo'        => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            ], [
                'vendor_name.max' => 'Vendor name must not exceed 255 characters.',
                'phone.max'       => 'Phone number must not exceed 15 digits.',
                'gstin.max'       => 'GST number must not exceed 15 characters.',
                'address.max'     => 'Address must not exceed 500 characters.',
                'logo.mimes'      => 'Logo must be JPG or PNG.',
                'logo.max'        => 'Logo size must not exceed 2MB.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $user   = $request->user();
            $tenant = $user->tenant;

            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant profile not found.',
                ], 404);
            }

            $tenantUpdate = [];
            $userUpdate   = [];

            if ($request->filled('vendor_name')) {
                $tenantUpdate['company_name'] = $request->vendor_name;
                $userUpdate['name']           = $request->vendor_name;
            }

            if ($request->filled('phone')) {
                $tenantUpdate['phone'] = $request->phone;
                $userUpdate['phone']   = $request->phone;
            }

            if ($request->filled('gstin')) {
                $tenantUpdate['gstin'] = $request->gstin;
            }

            if ($request->filled('address')) {
                $tenantUpdate['address'] = $request->address;
            }

            // Logo upload
            if ($request->hasFile('logo')) {
                try {
                    // Delete old logo
                    if ($tenant->logo_path) {
                        $oldPath = 'public/' . $tenant->logo_path;
                        if (Storage::exists($oldPath)) {
                            Storage::delete($oldPath);
                        }
                    }

                    $file     = $request->file('logo');
                    $fileName = 'logo-' . $tenant->id . '-' . now()->format('YmdHis') . '.' . $file->extension();
                    $stored   = $file->storeAs('public/tenants/logos', $fileName);
                    $tenantUpdate['logo_path'] = str_replace('public/', '', $stored);
                } catch (\Exception $logoEx) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Logo upload failed: ' . $logoEx->getMessage(),
                    ], 500);
                }
            }

            // Kuch bhi update nahi karna
            if (empty($tenantUpdate) && empty($userUpdate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update.',
                ], 422);
            }

            if (!empty($tenantUpdate)) {
                $tenant->update($tenantUpdate);
            }

            if (!empty($userUpdate)) {
                $user->update($userUpdate);
            }

            $tenant->refresh();
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data'    => [
                    'user' => [
                        'id'           => $user->id,
                        'name'         => $user->name,
                        'email'        => $user->email,
                        'phone'        => $user->phone,
                        'role'         => $user->role,
                        'tenant_id'    => $tenant->id,
                        'company_name' => $tenant->company_name,
                        'gstin'        => $tenant->gstin,
                        'address'      => $tenant->address,
                        'logo_url'     => $tenant->logo_url,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // LOGIN STEP 1 — Send OTP
    // POST /api/auth/send-otp
    // ─────────────────────────────────────────────────
    public function sendLoginOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ], [
                'email.required' => 'Email address is required.',
                'email.email'    => 'Please enter a valid email address.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email address.',
                ], 404);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been disabled. Please contact support.',
                ], 403);
            }

            if ($user->tenant_id && !$user->tenant?->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact support.',
                ], 403);
            }

            $this->otpService->sendLoginOtp($user);

            return response()->json([
                'success' => true,
                'message' => 'OTP has been sent to ' . $this->otpService->maskEmail($user->email) . '. Please check your email.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // LOGIN STEP 2 — Verify OTP
    // POST /api/auth/verify-login-otp
    // ─────────────────────────────────────────────────
    public function verifyLoginOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp'   => 'required|digits:6',
            ], [
                'email.required' => 'Email address is required.',
                'email.email'    => 'Please enter a valid email address.',
                'otp.required'   => 'OTP is required.',
                'otp.digits'     => 'OTP must be exactly 6 digits.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email address.',
                ], 404);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been disabled. Please contact support.',
                ], 403);
            }

            $result = $this->otpService->verify($request->email, $request->otp, 'login');

            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            $user->tokens()->delete();
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful. Welcome back, ' . $user->name . '!',
                'token'   => $token,
                'user'    => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'phone'        => $user->phone,
                    'role'         => $user->role,
                    'tenant_id'    => $user->tenant_id,
                    'company_name' => $user->tenant?->company_name,
                    'gstin'        => $user->tenant?->gstin,
                    'address'      => $user->tenant?->address,
                    'logo_url'     => $user->tenant?->logo_url,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // RESEND LOGIN OTP
    // POST /api/auth/resend-login-otp
    // ─────────────────────────────────────────────────
    public function resendLoginOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ], [
                'email.required' => 'Email address is required.',
                'email.email'    => 'Please enter a valid email address.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email address.',
                ], 404);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been disabled. Please contact support.',
                ], 403);
            }

            $this->otpService->sendLoginOtp($user);

            return response()->json([
                'success' => true,
                'message' => 'A new OTP has been sent to ' . $this->otpService->maskEmail($user->email) . '.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // FORGOT PASSWORD STEP 1
    // POST /api/auth/forgot-password
    // ─────────────────────────────────────────────────
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ], [
                'email.required' => 'Email address is required.',
                'email.email'    => 'Please enter a valid email address.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email address.',
                ], 404);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been disabled. Please contact support.',
                ], 403);
            }

            $this->otpService->sendForgotOtp($user);

            return response()->json([
                'success' => true,
                'message' => 'Password reset OTP has been sent to ' . $this->otpService->maskEmail($user->email) . '.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // FORGOT PASSWORD STEP 2 — Verify OTP only
    // POST /api/auth/verify-forgot-otp
    // ─────────────────────────────────────────────────
    public function verifyForgotOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp'   => 'required|digits:6',
            ], [
                'email.required' => 'Email address is required.',
                'email.email'    => 'Please enter a valid email address.',
                'otp.required'   => 'OTP is required.',
                'otp.digits'     => 'OTP must be exactly 6 digits.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email address.',
                ], 404);
            }

            // Sirf check — mark as used mat karo
            $result = $this->otpService->check($request->email, $request->otp, 'forgot_password');

            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully. Please set your new password.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'OTP verification failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // FORGOT PASSWORD STEP 3 — Reset Password
    // POST /api/auth/reset-password
    // ─────────────────────────────────────────────────
    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email'                 => 'required|email',
                'otp'                   => 'required|digits:6',
                'password'              => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string',
            ], [
                'email.required'                 => 'Email address is required.',
                'email.email'                    => 'Please enter a valid email address.',
                'otp.required'                   => 'OTP is required.',
                'otp.digits'                     => 'OTP must be exactly 6 digits.',
                'password.required'              => 'New password is required.',
                'password.min'                   => 'Password must be at least 8 characters.',
                'password.confirmed'             => 'Password and confirm password do not match.',
                'password_confirmation.required' => 'Please confirm your new password.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this email address.',
                ], 404);
            }

            // Final verify + mark as used
            $result = $this->otpService->verify($request->email, $request->otp, 'forgot_password');

            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            $user->update(['password' => Hash::make($request->password)]);
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully. Please login with your new password.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // LOGOUT
    // POST /api/v1/auth/logout
    // ─────────────────────────────────────────────────
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // ME — Current user info
    // GET /api/v1/auth/me
    // ─────────────────────────────────────────────────
    public function me(Request $request)
    {
        try {
            $user = $request->user()->load('tenant');

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'phone'        => $user->phone,
                    'role'         => $user->role,
                    'tenant_id'    => $user->tenant_id,
                    'company_name' => $user->tenant?->company_name,
                    'gstin'        => $user->tenant?->gstin,
                    'address'      => $user->tenant?->address,
                    'logo_url'     => $user->tenant?->logo_url,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user info.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
