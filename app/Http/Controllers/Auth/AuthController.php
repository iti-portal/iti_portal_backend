<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Auth\Events\Verified;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $user = Auth::user();

            $message = 'Login successful';
            if (!$user->isVerified()){
                $message = 'Please verify your email to complete the login.';
            } else if (!$user->isApproved()){
                $message = 'Your account is not approved yet. You will receive an email once it is approved.';
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
                    'isApproved' => $user->isApproved(),
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
        //
    }

    public function resendVerificationEmail(Request $request)
    {
        //
    }
   
}
