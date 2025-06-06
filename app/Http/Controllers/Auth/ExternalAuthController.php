<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExternalAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'redirect_url' => 'required|url',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            if (!$user->isApproved()) {
                return redirect($request->redirect_url . '?error=account_not_approved');
            }

            if ($user->getRegistrationStep() !== 'completed') {
                return redirect($request->redirect_url . '?error=registration_incomplete');
            }

            $token = JWTAuth::fromUser($user);

            return redirect($request->redirect_url . '?token=' . $token);
        }

        return redirect($request->redirect_url . '?error=invalid_credentials');
    }

    public function verifyToken(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            return response()->json([
                'valid' => true,
                'user' => $user->load('profile', 'companyProfile', 'roles'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    public function refreshToken()
    {
        try {
            $newToken = JWTAuth::refresh();
            
            return response()->json([
                'token' => $newToken,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Could not refresh token',
            ], 401);
        }
    }
}
