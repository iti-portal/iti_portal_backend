<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email",
        ]);
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT ? response()->json([
            'message' => __($status),
        ], 200) : response()->json([
            'message' => __($status),
        ], 400);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
                $user->tokens()->delete();
            }

        );
        return $status === Password::PASSWORD_RESET ? response()->json([
            'message' => __($status),
        ], 200) : response()->json([
            'message' => __($status),
        ], 400);

    }

}
