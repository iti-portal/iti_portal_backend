<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowRegistrationStep
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$allowedSteps): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $currentStep = $user->getRegistrationStep();

        if (!in_array($currentStep, $allowedSteps)) {
            return response()->json([
                'error' => 'Invalid registration step',
                'current_step' => $currentStep,
                'allowed_steps' => $allowedSteps,
                'message' => 'You cannot access this resource at your current registration step.'
            ], 403);
        }

        return $next($request);
    }
}
