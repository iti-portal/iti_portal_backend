<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $step = $user->getRegistrationStep();

        // If registration is not completed, block access
        if (in_array($step, ['user_profile', 'company_profile', 'nid_upload'])) {
            return response()->json([
                'error' => 'Profile incomplete',
                'step' => $step,
                'message' => 'Please complete your profile before accessing this resource.',
                'redirect' => $this->getRedirectUrl($step)
            ], 403);
        }

        return $next($request);
    }

    private function getRedirectUrl($step)
    {
        return match($step) {
            'user_profile' => '/complete-profile',
            'company_profile' => '/complete-company-profile',
            'nid_upload' => '/upload-nid',
            // default => '/dashboard'
        };
    }
}
