<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountSecurityRequest;
use App\Mail\VerifyNewEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    //
 

    public function updateAccount(UpdateAccountSecurityRequest $request)
    {
        $user = auth()->user();
        $emailChanged = false;
        $passwordChanged = false;
        if (!$user) {
            return $this->respondWithError('User not found', 404);
        }

        try {
            if ($request->filled('email') && $request->email !== $user->email) {
                $newEmail = $request->email;
               
                $url = URL::temporarySignedRoute(
                    'verify-new-email',
                    now()->addHours(24),
                    ['user' => $user->id]
                );

                Mail::to($newEmail)->send(new VerifyNewEmail($user, $url));
                $user->new_email = $newEmail;
                $emailChanged = true;
            }

            if ($request->filled('new_password')) {
                if (!Hash::check($request->password, $user->password)) {
                    return $this->respondWithError('Current password is incorrect', 400);
                }

                if (Hash::check($request->new_password, $user->password)) {
                    return $this->respondWithError('New password must be different from the current one', 400);
                }

                $user->password = Hash::make($request->new_password);
                $passwordChanged = true;
            }

            $user->save();
            

            $editedUser = User::with('profile')->find($user->id);
            if(!$emailChanged && !$passwordChanged) {
                return $this->respondWithSuccess(['user' => $editedUser], 'You did not change anything');
            }
            elseif($emailChanged){
                return $this->respondWithSuccess(['user' => $editedUser], 'We sent you an email to verify your new email that expires in 24 hours');
            }
             if ($newEmail === $user->new_email) {
                if($passwordChanged)
                    return $this->respondWithSuccess(['user' => $editedUser],'Password has been changed successfully, we already sent you a verification email to this email', 400);
            }
           
            return $this->respondWithSuccess(['user' => $editedUser], 'Account has been updated successfully');
        } catch (\Exception $e) {
            return $this->respondWithError("Error: " . $e->getMessage(), 500);
        }
    }

    public function updateEmail(Request $request){
        $user = auth()->user();
        if (!$user) {
            return $this->respondWithError('User not found', 404);
        }
        $request->merge([
            'email' => trim($request->email),
            'password' => trim($request->password),
        ]);
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['required']
        ]);
        $email = $request->email;
        $password = $request->password;
        if ($email === $user->email) {
            return $this->respondWithError('New email must be different from the current one', 400);
        }
        if (!Hash::check($password, $user->password)) {
            return $this->respondWithError('Current password is incorrect', 400);
        }
        $query = URL::temporarySignedRoute(
            'verify-new-email',
            now()->addHours(24),
            ['user' => $user->id]
        );
        try {
            Mail::to($email)->send(new VerifyNewEmail($user, $query));
            $user->email = $email;
        
            $user->save();
            $editedUser = User::with('profile')->find($user->id);
            return $this->respondWithSuccess(['user' => $editedUser], 'We sent you an email to verify your new email that expires in 24 hours');                                                         

        } catch (\Exception $e) {
            return $this->respondWithError("Error: " . $e->getMessage(), 500);
        }
        
    }






    public function verifyNewEmail(Request $request, $user){
        if(!$request->hasValidSignature()){
            return $this->respondWithError('Invalid or expired verification link', 400);
        }
        
        try{
            $user = User::findOrFail($user);
            if(!$user->new_email){
                return $this->respondWithError('No pending email found', 404);
            }
        $user->email = $user->new_email;
        $user->new_email = null;
        $user->save();
        return $this->respondWithSuccess([], 'Email verified successfully');
    
        }catch (\Exception $e) {
            return $this->respondWithError("Something went wrong", 500);
        }
    }
}
