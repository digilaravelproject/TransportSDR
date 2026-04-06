<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    // ─────────────────────────────────────────────────────
    // LOGIN STEP 1 — Send OTP to email
    // POST /api/auth/send-otp
    // Body: { email }
    // ─────────────────────────────────────────────────────
    public function sendLoginOtp(Request $request)
    {
        // Step 1 — Validate input
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email address is required.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

        // Step 2 — Check email exists in database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address. Please check your email or contact support.',
            ], 404);
        }

        // Step 3 — Check account is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been disabled. Please contact support.',
            ], 403);
        }

        // Step 4 — Check tenant subscription
        if ($user->tenant_id && !$user->tenant?->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Your subscription has expired. Please contact support to renew.',
            ], 403);
        }

        // Step 5 — Send OTP
        try {
            $this->otpService->sendLoginOtp($user);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again after some time.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP has been sent to ' . $this->otpService->maskEmail($user->email) . '. Please check your email.',
        ]);
    }

    // ─────────────────────────────────────────────────────
    // LOGIN STEP 2 — Verify OTP, return token
    // POST /api/auth/verify-login-otp
    // Body: { email, otp }
    // ─────────────────────────────────────────────────────
    public function verifyLoginOtp(Request $request)
    {
        // Step 1 — Validate input
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ], [
            'email.required' => 'Email address is required.',
            'email.email'    => 'Please enter a valid email address.',
            'otp.required'   => 'OTP is required.',
            'otp.digits'     => 'OTP must be exactly 6 digits.',
        ]);

        // Step 2 — Check email exists in database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.',
            ], 404);
        }

        // Step 3 — Check account is still active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been disabled. Please contact support.',
            ], 403);
        }

        // Step 4 — Verify OTP
        $result = $this->otpService->verify(
            $request->email,
            $request->otp,
            'login'
        );

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        // Step 5 — Generate token (single device — delete old tokens)
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
                'role'         => $user->role,
                'tenant_id'    => $user->tenant_id,
                'company_name' => $user->tenant?->company_name,
                'plan'         => $user->tenant?->plan,
                'plan_expires' => $user->tenant?->plan_expires_at?->format('d-m-Y'),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────
    // LOGIN OTP RESEND
    // POST /api/auth/resend-login-otp
    // Body: { email }
    // ─────────────────────────────────────────────────────
    public function resendLoginOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email address is required.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

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

        try {
            $this->otpService->sendLoginOtp($user);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'A new OTP has been sent to ' . $this->otpService->maskEmail($user->email) . '.',
        ]);
    }

    // ─────────────────────────────────────────────────────
    // FORGOT PASSWORD STEP 1 — Send OTP
    // POST /api/auth/forgot-password
    // Body: { email }
    // ─────────────────────────────────────────────────────
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email address is required.',
            'email.email'    => 'Please enter a valid email address.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address. Please check your email.',
            ], 404);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been disabled. Please contact support.',
            ], 403);
        }

        try {
            $this->otpService->sendForgotOtp($user);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset OTP has been sent to ' . $this->otpService->maskEmail($user->email) . '.',
        ]);
    }

    // ─────────────────────────────────────────────────────
    // FORGOT PASSWORD STEP 2 — Verify OTP only
    // POST /api/auth/verify-forgot-otp
    // Body: { email, otp }
    // ─────────────────────────────────────────────────────
    public function verifyForgotOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ], [
            'email.required' => 'Email address is required.',
            'email.email'    => 'Please enter a valid email address.',
            'otp.required'   => 'OTP is required.',
            'otp.digits'     => 'OTP must be exactly 6 digits.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.',
            ], 404);
        }

        // Only check — do not mark as used yet
        $result = $this->otpService->check(
            $request->email,
            $request->otp,
            'forgot_password'
        );

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
    }

    // ─────────────────────────────────────────────────────
    // FORGOT PASSWORD STEP 3 — Reset password
    // POST /api/auth/reset-password
    // Body: { email, otp, password, password_confirmation }
    // ─────────────────────────────────────────────────────
    public function resetPassword(Request $request)
    {
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.',
            ], 404);
        }

        // Final verify and mark OTP as used
        $result = $this->otpService->verify(
            $request->email,
            $request->otp,
            'forgot_password'
        );

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all tokens for security
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully. Please login with your new password.',
        ]);
    }

    // ─────────────────────────────────────────────────────
    // LOGOUT
    // POST /api/v1/auth/logout
    // ─────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────
    // ME — Get current logged in user
    // GET /api/v1/auth/me
    // ─────────────────────────────────────────────────────
    public function me(Request $request)
    {
        $user = $request->user()->load('tenant');

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'role'         => $user->role,
                'tenant_id'    => $user->tenant_id,
                'company_name' => $user->tenant?->company_name,
                'plan'         => $user->tenant?->plan,
                'plan_expires' => $user->tenant?->plan_expires_at?->format('d-m-Y'),
            ],
        ]);
    }
}
