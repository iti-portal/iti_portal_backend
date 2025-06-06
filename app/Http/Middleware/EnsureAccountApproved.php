<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountApproved
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

        if (!$user->isApproved()) {
            $message = match($user->status) {
                'pending' => 'Your account is pending approval. Please wait for admin approval.',
                'rejected' => 'Your account has been rejected. Please contact support.',
                default => 'Your account is not approved for access.'
            };

            return response()->json([
                'error' => 'Account not approved',
                'status' => $user->status,
                'message' => $message
            ], 403);
        }

        return $next($request);
    }
}
