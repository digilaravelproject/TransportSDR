<?php
// app/Http/Controllers/Api/AuthController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{User, Tenant};
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, DB, Storage};
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    // ─────────────────────────────────────────────────
    // REGISTER STEP 1 — Send OTP to email
    // POST /api/auth/register/send-otp
    // Body: { vendor_name, owner_name, phone, email }
    // ─────────────────────────────────────────────────
    // public function registerSendOtp(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'vendor_name' => 'required|string|max:255',
    //             'owner_name'  => 'required|string|max:255',
    //             'phone'       => 'required|digits:10',
    //             'email'       => 'required|email|unique:users,email',
    //         ], [
    //             'vendor_name.required' => 'Vendor name is required.',
    //             'owner_name.required'  => 'Owner name is required.',
    //             'phone.required'       => 'Mobile number is required.',
    //             'phone.digits'         => 'Mobile number must be exactly 10 digits.',
    //             'email.required'       => 'Email address is required.',
    //             'email.email'          => 'Please enter a valid email address.',
    //             'email.unique'         => 'This email is already registered. Please sign in.',
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'errors'  => $e->errors(),
    //         ], 422);
    //     }

    //     try {
    //         $this->otpService->sendRegistrationOtp(
    //             $request->email,
    //             $request->owner_name
    //         );

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'OTP has been sent to ' . $this->otpService->maskEmail($request->email) . '. Please verify to complete registration.',
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to send OTP. Please try again.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function registerSendOtp(Request $request)
    // {
    //     // 1. Validation
    //     try {
    //         $request->validate([
    //             'vendor_name' => 'required|string|max:255',
    //             'owner_name'  => 'required|string|max:255',
    //             'phone'       => 'required|digits:10',
    //             'email'       => 'required|email|unique:users,email',
    //         ], [
    //             'email.unique' => 'This email is already registered. Please login via OTP.',
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'errors'  => $e->errors(),
    //         ], 422);
    //     }

    //     try {
    //         // 2. Database mein data save karna (Tenant + User)
    //         DB::transaction(function () use ($request) {
    //             // Create Tenant
    //             $tenant = Tenant::create([
    //                 'company_name' => $request->vendor_name,
    //                 'owner_name'   => $request->owner_name,
    //                 'email'        => $request->email,
    //                 'phone'        => $request->phone,
    //                 'is_active'    => true,
    //             ]);

    //             // Create User (Password random string set kar rahe hain kyunki OTP login hai)
    //             User::create([
    //                 'tenant_id' => $tenant->id,
    //                 'name'      => $request->owner_name,
    //                 'email'     => $request->email,
    //                 'phone'     => $request->phone,
    //                 'password'  => bcrypt(Str::random(16)),
    //                 'role'      => 'admin',
    //                 'is_active' => true,
    //             ]);
    //         });

    //         // 3. OTP Send Karna
    //         // Note: 'login' type use kar rahe hain taaki verifyLoginOtp isse verify kar sake
    //         $this->otpService->sendRegistrationOtp(
    //             $request->email,
    //             $request->owner_name
    //         );

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Registration successful! OTP has been sent to ' . $this->otpService->maskEmail($request->email) . '. Please verify to login.',
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // registerSendOtp — OTP type 'login' use karo
    public function registerSendOtp(Request $request)
    {
        // 1. Validation
        try {
            $request->validate([
                'vendor_name' => 'required|string|max:255',
                'owner_name'  => 'required|string|max:255',
                'phone'       => 'required|digits:10',
                'email'       => 'required|email|unique:users,email',
            ], [
                'vendor_name.required' => 'Vendor name is required.',
                'owner_name.required'  => 'Owner name is required.',
                'phone.required'       => 'Mobile number is required.',
                'phone.digits'         => 'Mobile number must be exactly 10 digits.',
                'email.required'       => 'Email address is required.',
                'email.email'          => 'Please enter a valid email address.',
                'email.unique'         => 'This email is already registered. Please login via OTP.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            // 2. Tenant + User save karo
            DB::transaction(function () use ($request) {
                $tenant = Tenant::create([
                    'company_name' => $request->vendor_name,
                    'owner_name'   => $request->owner_name,
                    'email'        => $request->email,
                    'phone'        => $request->phone,
                    'is_active'    => true,
                ]);

                User::create([
                    'tenant_id' => $tenant->id,
                    'name'      => $request->owner_name,
                    'email'     => $request->email,
                    'phone'     => $request->phone,
                    'password'  => bcrypt(\Illuminate\Support\Str::random(16)),
                    'role'      => 'admin',
                    'is_active' => true,
                ]);
            });

            // 3. OTP bhejo — 'login' type use karo
            // Ab verifyLoginOtp seedha kaam karega
            $user = User::where('email', $request->email)->first();
            $this->otpService->sendLoginOtp($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! OTP has been sent to ' . $this->otpService->maskEmail($request->email) . '. Please verify to login.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // REGISTER STEP 2 — Verify OTP + Complete Registration
    // POST /api/auth/register/verify
    // Body: { vendor_name, owner_name, phone, email, otp, password, password_confirmation }
    // ─────────────────────────────────────────────────
    // public function registerVerify(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'vendor_name'           => 'required|string|max:255',
    //             'owner_name'            => 'required|string|max:255',
    //             'phone'                 => 'required|digits:10',
    //             'email'                 => 'required|email|unique:users,email',
    //             'otp'                   => 'required|digits:6',
    //             // 'password'              => 'required|string|min:8|confirmed',
    //             // 'password_confirmation' => 'required|string',
    //         ], [
    //             'vendor_name.required'           => 'Vendor name is required.',
    //             'owner_name.required'            => 'Owner name is required.',
    //             'phone.required'                 => 'Mobile number is required.',
    //             'phone.digits'                   => 'Mobile number must be exactly 10 digits.',
    //             'email.required'                 => 'Email address is required.',
    //             'email.email'                    => 'Please enter a valid email address.',
    //             'email.unique'                   => 'This email is already registered. Please sign in.',
    //             'otp.required'                   => 'OTP is required.',
    //             'otp.digits'                     => 'OTP must be exactly 6 digits.',
    //             // 'password.required'              => 'Password is required.',
    //             // 'password.min'                   => 'Password must be at least 8 characters.',
    //             // 'password.confirmed'             => 'Password and confirm password do not match.',
    //             // 'password_confirmation.required' => 'Please confirm your password.',
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'errors'  => $e->errors(),
    //         ], 422);
    //     }

    //     try {
    //         // Verify OTP
    //         $result = $this->otpService->verify(
    //             $request->email,
    //             $request->otp,
    //             'registration'
    //         );

    //         if (!$result['valid']) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => $result['message'],
    //             ], 422);
    //         }

    //         return DB::transaction(function () use ($request) {

    //             // 1. Create Tenant
    //             $tenant = Tenant::create([
    //                 'company_name' => $request->vendor_name,
    //                 'owner_name'   => $request->owner_name,
    //                 'email'        => $request->email,
    //                 'phone'        => $request->phone,
    //                 'is_active'    => true,
    //             ]);

    //             // 2. Create Admin User
    //             $user = User::create([
    //                 'tenant_id' => $tenant->id,
    //                 'name'      => $request->owner_name,
    //                 'email'     => $request->email,
    //                 'phone'     => $request->phone,
    //                 'password'  => Hash::make($request->password),
    //                 'role'      => 'admin',
    //                 'is_active' => true,
    //             ]);

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Registration successful. You can now login.',
    //                 'data'    => [
    //                     'user' => [
    //                         'id'           => $user->id,
    //                         'name'         => $user->name,
    //                         'email'        => $user->email,
    //                         'phone'        => $user->phone,
    //                         'role'         => $user->role,
    //                         'tenant_id'    => $tenant->id,
    //                         'company_name' => $tenant->company_name,
    //                         'owner_name'   => $tenant->owner_name,
    //                     ],
    //                 ],
    //             ], 201);
    //         });
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Registration failed. Please try again.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // ─────────────────────────────────────────────────
    // REGISTER OTP RESEND
    // POST /api/auth/register/resend-otp
    // Body: { owner_name, email }
    // ─────────────────────────────────────────────────
    public function registerResendOtp(Request $request)
    {
        try {
            $request->validate([
                'owner_name' => 'required|string|max:255',
                'email'      => 'required|email',
            ], [
                'owner_name.required' => 'Owner name is required.',
                'email.required'      => 'Email address is required.',
                'email.email'         => 'Please enter a valid email address.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            // Check email already registered nahi ho
            $exists = User::where('email', $request->email)->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered. Please sign in.',
                ], 422);
            }

            $this->otpService->sendRegistrationOtp(
                $request->email,
                $request->owner_name
            );

            return response()->json([
                'success' => true,
                'message' => 'A new OTP has been sent to ' . $this->otpService->maskEmail($request->email) . '.',
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
    // EDIT PROFILE
    // POST /api/v1/auth/profile/update
    // Fields: gstin, address, logo (all optional)
    // ─────────────────────────────────────────────────
    // public function updateProfile(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'gstin'   => 'nullable|string|max:15',
    //             'address' => 'nullable|string|max:500',
    //             'logo'    => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
    //         ], [
    //             'gstin.max'    => 'GST number must not exceed 15 characters.',
    //             'address.max'  => 'Address must not exceed 500 characters.',
    //             'logo.mimes'   => 'Logo must be JPG or PNG.',
    //             'logo.max'     => 'Logo size must not exceed 2MB.',
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'errors'  => $e->errors(),
    //         ], 422);
    //     }

    //     try {
    //         $user   = $request->user();
    //         $tenant = $user->tenant;

    //         if (!$tenant) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Tenant profile not found.',
    //             ], 404);
    //         }

    //         $tenantUpdate = [];

    //         // Only update fields that are sent
    //         if ($request->filled('gstin')) {
    //             $tenantUpdate['gstin'] = $request->gstin;
    //         }

    //         if ($request->filled('address')) {
    //             $tenantUpdate['address'] = $request->address;
    //         }

    //         // Logo upload
    //         if ($request->hasFile('logo')) {
    //             try {
    //                 // Delete old logo
    //                 if ($tenant->logo_path) {
    //                     $oldPath = 'public/' . $tenant->logo_path;
    //                     if (Storage::exists($oldPath)) {
    //                         Storage::delete($oldPath);
    //                     }
    //                 }

    //                 $file     = $request->file('logo');
    //                 $fileName = 'logo-' . $tenant->id . '-' . now()->format('YmdHis') . '.' . $file->extension();
    //                 $stored = $file->storeAs('tenants/logos', $fileName, 'public');
    //                 $tenantUpdate['logo_path'] = str_replace('public/', '', $stored);
    //             } catch (\Exception $logoEx) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Logo upload failed: ' . $logoEx->getMessage(),
    //                 ], 500);
    //             }
    //         }

    //         // Kuch bhi update nahi karna
    //         if (empty($tenantUpdate)) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'No data provided to update. Please send gstin, address or logo.',
    //             ], 422);
    //         }

    //         $tenant->update($tenantUpdate);
    //         $tenant->refresh();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Profile updated successfully.',
    //             'data'    => [
    //                 'tenant_id'    => $tenant->id,
    //                 'company_name' => $tenant->company_name,
    //                 'owner_name'   => $tenant->owner_name,
    //                 'email'        => $tenant->email,
    //                 'phone'        => $tenant->phone,
    //                 'gstin'        => $tenant->gstin,
    //                 'address'      => $tenant->address,
    //                 'logo_url'     => $tenant->logo_url,
    //             ],
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Profile update failed. Please try again.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'vendor_name' => 'nullable|string|max:255',
                'owner_name'  => 'nullable|string|max:255',
                'phone'       => 'nullable|digits:10',
                'gstin'       => 'nullable|string|max:15',
                'address'     => 'nullable|string|max:500',
                'logo'        => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            ], [
                'vendor_name.max' => 'Vendor name must not exceed 255 characters.',
                'owner_name.max'  => 'Owner name must not exceed 255 characters.',
                'phone.digits'    => 'Phone number must be 10 digits.',
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

            // Tenant fields update
            if ($request->filled('vendor_name')) {
                $tenantUpdate['company_name'] = $request->vendor_name;
            }

            if ($request->filled('gstin')) {
                $tenantUpdate['gstin'] = $request->gstin;
            }

            if ($request->filled('address')) {
                $tenantUpdate['address'] = $request->address;
            }

            // User fields update
            if ($request->filled('owner_name')) {
                $tenantUpdate['owner_name'] = $request->owner_name;
                $userUpdate['name'] = $request->owner_name;
            }

            if ($request->filled('phone')) {
                $tenantUpdate['phone'] = $request->phone;
            }

            // Logo upload
            if ($request->hasFile('logo')) {
                try {
                    if ($tenant->logo_path) {
                        $oldPath = 'public/' . $tenant->logo_path;
                        if (Storage::exists($oldPath)) {
                            Storage::delete($oldPath);
                        }
                    }

                    $file     = $request->file('logo');
                    $fileName = 'logo-' . $tenant->id . '-' . now()->format('YmdHis') . '.' . $file->extension();
                    $stored   = $file->storeAs('tenants/logos', $fileName, 'public');

                    $tenantUpdate['logo_path'] = $stored;
                } catch (\Exception $logoEx) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Logo upload failed: ' . $logoEx->getMessage(),
                    ], 500);
                }
            }

            if (empty($tenantUpdate) && empty($userUpdate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided to update. Please send required fields.',
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
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'tenant_id'    => $tenant->id,
                    'company_name' => $tenant->company_name,
                    'owner_name'   => $tenant->owner_name,
                    'email'        => $tenant->email,
                    'phone'        => $tenant->phone,
                    'gstin'        => $tenant->gstin,
                    'address'      => $tenant->address,
                    'logo_url'     => $tenant->logo_url,
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
    // public function verifyLoginOtp(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'email' => 'required|email',
    //             'otp'   => 'required|digits:6',
    //         ], [
    //             'email.required' => 'Email address is required.',
    //             'email.email'    => 'Please enter a valid email address.',
    //             'otp.required'   => 'OTP is required.',
    //             'otp.digits'     => 'OTP must be exactly 6 digits.',
    //         ]);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'errors'  => $e->errors(),
    //         ], 422);
    //     }

    //     try {
    //         $user = User::where('email', $request->email)->first();

    //         if (!$user) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'No account found with this email address.',
    //             ], 404);
    //         }

    //         if (!$user->is_active) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Your account has been disabled. Please contact support.',
    //             ], 403);
    //         }

    //         $result = $this->otpService->verify($request->email, $request->otp, 'login');

    //         if (!$result['valid']) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => $result['message'],
    //             ], 422);
    //         }

    //         $user->tokens()->delete();
    //         $token = $user->createToken('api-token')->plainTextToken;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Login successful. Welcome back, ' . $user->name . '!',
    //             'token'   => $token,
    //             'user'    => [
    //                 'id'           => $user->id,
    //                 'name'         => $user->name,
    //                 'email'        => $user->email,
    //                 'phone'        => $user->tenant?->phone,
    //                 'role'         => $user->role,
    //                 'tenant_id'    => $user->tenant_id,
    //                 'company_name' => $user->tenant?->company_name,
    //                 'owner_name'   => $user->tenant?->owner_name,
    //                 'gstin'        => $user->tenant?->gstin,
    //                 'address'      => $user->tenant?->address,
    //                 'logo_url'     => $user->tenant?->logo_url,
    //             ],
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Login failed. Please try again.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
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
            // Yahan with('tenant') add kiya hai taaki tenant relation load ho jaye
            $user = User::with('tenant')->where('email', $request->email)->first();

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
                    'phone'        => $user->tenant?->phone, // Ab ye value correctly mil jayegi
                    'role'         => $user->role,
                    'tenant_id'    => $user->tenant_id,
                    'company_name' => $user->tenant?->company_name,
                    'owner_name'   => $user->tenant?->owner_name,
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
    // FORGOT PASSWORD STEP 2
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
    // FORGOT PASSWORD STEP 3
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
    // ME
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
                    'phone'        => $user->tenant?->phone,
                    'role'         => $user->role,
                    'tenant_id'    => $user->tenant_id,
                    'company_name' => $user->tenant?->company_name,
                    'owner_name'   => $user->tenant?->owner_name,
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

    // ─────────────────────────────────────────────────
    // SIGNUP — Agency/User Registration
    // POST /api/auth/signup
    // ─────────────────────────────────────────────────
    public function signup(Request $request)
    {
        try {
            $validated = $request->validate([
                'agency_name' => 'required|string|max:255',
                'owner_name' => 'required|string|max:255',
                'mobile_number' => 'required|regex:/^[0-9]{10}$/|unique:users,phone_number',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string',
            ], [
                'agency_name.required' => 'Agency name is required.',
                'agency_name.max' => 'Agency name must not exceed 255 characters.',
                'owner_name.required' => 'Owner name is required.',
                'owner_name.max' => 'Owner name must not exceed 255 characters.',
                'mobile_number.required' => 'Mobile number is required.',
                'mobile_number.regex' => 'Mobile number must be exactly 10 digits.',
                'mobile_number.unique' => 'This mobile number is already registered.',
                'email.required' => 'Email address is required.',
                'email.email' => 'Please provide a valid email address.',
                'email.unique' => 'This email address is already registered.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'Password and confirm password do not match.',
                'password_confirmation.required' => 'Please confirm your password.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            return DB::transaction(function () use ($validated) {
                // 1. Create Tenant (Agency)
                $tenant = Tenant::create([
                    'company_name' => $validated['agency_name'],
                    'email'        => $validated['email'],
                    'phone'        => $validated['mobile_number'],
                    'is_active'    => true,
                ]);

                // 2. Create Admin User
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name'      => $validated['owner_name'],
                    'email'     => $validated['email'],
                    'phone_number'     => $validated['mobile_number'],
                    'password'  => Hash::make($validated['password']),
                    'role'      => 'admin',
                    'is_active' => true,
                ]);

                // 3. Generate API Token
                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Signup successful! Welcome to your agency account.',
                    'data'    => [
                        'user' => [
                            'id'           => $user->id,
                            'name'         => $user->name,
                            'email'        => $user->email,
                            'phone_number' => $user->phone_number,
                            'role'         => $user->role,
                            'tenant_id'    => $tenant->id,
                            'agency_name'  => $tenant->company_name,
                        ],
                        'token' => $token,
                    ],
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Signup failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
