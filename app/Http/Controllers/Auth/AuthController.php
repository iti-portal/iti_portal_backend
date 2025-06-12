<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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

            // Check registration completion
            $step = $user->getRegistrationStep();

            if ($step === 'email_verification') {
                return response()->json([
                    'message' => 'Please verify your email.',
                    'step'    => $step,
                ]);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            if ($step !== 'completed') {
                return response()->json([
                    'message' => 'Please complete your registration.',
                    'step'    => $step,
                    'token'   => $token,
                ]);
            }

            if (! $user->isApproved()) {
                Auth::logout();
                return response()->json([
                    'message' => 'Your account is not approved yet.',
                ], 403);
            }

            // For API requests, return token
            return response()->json([
                'message' => 'Login successful',
                'user'    => $user->load('profile', 'companyProfile'),
                'token'   => $token,
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('profile', 'companyProfile', 'roles', 'permissions'),
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
