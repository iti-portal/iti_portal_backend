<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            if (!$user->isVerified()){
                $message = 'Please verify your email to complete the login.';
            } else if (!$user->isApproved()){
                $message = 'Your account is not approved yet. You will receive an email once it is approved.';
            } else if (!$user->isRejected()){
                $message = 'Your registration request has been rejected. You are not eligible for registration.';
            } else if (!$user->isSuspended()){
                $message = 'Your account is currently suspended. Contact ITI support for more information.';
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            if ($user->hasRole('admin') || $user->hasRole('staff')) {
                return response()->json([
                    'success' => true,
                    'message' => ucfirst($user->getRoleNames()->first()) . ' login successfully.',
                    'data' => [
                        'role' => $user->getRoleNames()->first(),
                        'token' => $token,
                    ],
                ],200);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'role' => $user->getRoleNames()->first(),
                    'isVerified' => $user->isVerified(),
                    'approval_status' => $user->status(),
                    'token' => $token,
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.',
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'data' => null,
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => [
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
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }
        // Mark email as verified
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return response()->json(['message' => 'Email verified successfully!']);


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
        try{
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

}
