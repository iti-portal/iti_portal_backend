<?php

use App\Http\Middleware\AllowRegistrationStep;
use App\Http\Middleware\CheckTokenExpiry;
use App\Http\Middleware\EnsureAccountApproved;
use App\Http\Middleware\EnsureEmailVerified;
use App\Http\Middleware\EnsureProfileComplete;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\JwtAuthMiddleware;
use App\Http\Middleware\PreventCompletedRegistration;
use Illuminate\Console\Scheduling\Schedule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.auth' => JwtAuthMiddleware::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            // Authentication & Authorization
            'email.verified' => EnsureEmailVerified::class,
            'profile.complete' => EnsureProfileComplete::class,
            'account.approved' => EnsureAccountApproved::class,
            'prevent.completed' => PreventCompletedRegistration::class,
            // Registration flow control
            'allow.step' => AllowRegistrationStep::class,
            // Token management
            'check.token.expiry' => CheckTokenExpiry::class,

        ]);
    })
    ->withSchedule(function(Schedule $schedule) {
        $schedule->command('users:delete-expired')
            ->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

