<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventCompletedRegistration
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
            return $next($request);
        }

        $step = $user->getRegistrationStep();

        if ($step === 'completed' && $user->isApproved()) {
            return response()->json([
                'error' => 'Registration already completed',
                'message' => 'You have already completed registration.',
                'redirect' => '/dashboard'
            ], 409);
        }

        return $next($request);
    }
}
