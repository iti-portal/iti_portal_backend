<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            
            // Check registration completion
            $step = $user->getRegistrationStep();
            
            if ($step === 'email_verification') {
                return response()->json([
                    'message' => 'Please verify your email.',
                    'step' => $step,
                ]);
            }
            
            $token = $user->createToken('auth-token')->plainTextToken;

            if ($step !== 'completed') {
                return response()->json([
                    'message' => 'Please complete your registration.',
                    'step' => $step,
                    'token' => $token
                ]);
            }

            if (!$user->isApproved()) {
                Auth::logout();
                return response()->json([
                    'message' => 'Your account is not approved yet.',
                ], 403);
            }

            // For API requests, return token
            return response()->json([
                'message' => 'Login successful',
                'user' => $user->load('profile', 'companyProfile'),
                'token' => $token,
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
            'message' => 'Logged out successfully'
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
        //
    }

    public function resendVerificationEmail(Request $request)
    {
        //
    }
}
