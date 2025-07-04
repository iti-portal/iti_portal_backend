<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $user = Auth::user();

            $message = 'Login successful';

            if ($user->isRejected()) {
                $message = 'Your registration request has been rejected. You are not eligible for registration.';
            } else if ($user->isSuspended()) {
                $message = 'Your account is currently suspended. Contact ITI support for more information.';
            } else if (! $user->isApproved()) {
                $message = 'Your account is not approved yet. You will receive an email once it is approved.';
            }

            // Check if the user is rejected, suspended, or verified and pending approval
            if ($user->isRejected() || $user->isSuspended() || ($user->isVerified() && ! $user->isApproved())) {
                $user->tokens()->delete();

                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }

            // If the user is not verified, set a message to prompt verification. Only if the user is not rejected or suspended
            if (! $user->isVerified()) {
                $message = 'Please verify your email to complete the login.';
            }

            try {
                DB::beginTransaction();

                $token = $user->createToken('auth-token')->plainTextToken;

                $tokenModel             = $user->tokens()->latest()->first();
                $tokenModel->expires_at = now()->addDays(1);
                $tokenModel->save();

                DB::commit();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to login. Please try again later.',
                ], 500);
            }

            // Admin/Staff login response
            if ($user->hasRole('admin') || $user->hasRole('staff')) {
                return response()->json([
                    'success' => true,
                    'message' => ucfirst($user->getRoleNames()->first()) . ' login successfully.',
                    'data'    => [
                        'role'  => $user->getRoleNames()->first(),
                        'token' => $token,
                    ],
                ], 200);
            }

            // Successful user login response
            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => [
                    'role'       => $user->getRoleNames()->first(),
                    'isVerified' => $user->isVerified(),
                    'token'      => $token,
                ],
            ], 200);
        }

        // If authentication fails, return an error response
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials. Email or password is incorrect.',
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'data'    => null,
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data'    => [
                'role' => $request->user()->getRoleNames()->first(),
                'user' => $request->user()->load('profile', 'companyProfile'),
            ],
        ]);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        if (! URL::hasValidSignature($request)) {
            return response()->json(['message' => 'Invalid or expired verification link. Please request a new one.'], 403);
        }
        // find user by ID
        $user = User::findOrFail($id);

        // verify if the hash is correct
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link.',
            ], 403);
        }
        // Check if the user is already verified
        if ($user->hasVerifiedEmail()) {
            redirect()->to(config('app.frontend_url') . '/login?verified=success')->with('message', 'Email already verified. Please login to continue.');
            // return response()->json([
            //     'message' => 'Email already verified.',
            // ], 400);
        }
        // Mark email as verified
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // return response()->json(['message' => 'Email verified successfully!']);
        return redirect()->to(config('app.frontend_url') . '/login?verified=success')->with('message', 'Email verified successfully! Please login to continue.');

    }

    public function resendVerificationEmail(Request $request)
    {
        // $user = User::where('email', $request->email)->first();
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }
        try {
            $user->sendEmailVerificationNotification();
            return response()->json([
                'message' => 'Verification email sent successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send verification email. Please try again later.',
            ], 500);
        }
    }
    public function externalLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if ($user->isRejected()) {
            return response()->json(['message' => 'Your registration request has been rejected.'], 403);
        }

        if ($user->isSuspended()) {
            return response()->json(['message' => 'Your account is suspended.'], 403);
        }
        if (! $user->isApproved()) {
            return response()->json(['message' => 'Your account is not approved yet.'], 403);
        }

        return response()->json([
            'message' => 'Login successful.',
            'data'    => [
                'id'         => $user->id,
                'email'      => $user->email,
                'name'       => $user->profile->full_name,
                'role'       => $user->getRoleNames()->first(),
                'first_name' => $user->profile->first_name,
                'last_name'  => $user->profile->last_name,
                'phone'      => $user->profile->phone,
                'track'      => $user->profile->track,
                'intake'     => $user->profile->intake,
                'github'     => $user->profile->github,
                'linkedin'   => $user->profile->linkedin,
            ],
        ]);
    }

}
