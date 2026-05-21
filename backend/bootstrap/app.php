<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withCommands([
        \App\Console\Commands\ScoutRun::class,
    ])
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        // Scout one random country every 10 minutes, up to 10 URLs per run.
        // Runs are skipped automatically if a previous run is still in progress.
        $schedule->command('scout:run --jobs=1 --max=10 --delay=1.5')
                 ->everyTenMinutes()
                 ->withoutOverlapping(5)   // skip if still running, release lock after 5 min
                 ->runInBackground()
                 ->appendOutputTo(base_path('../scout/scout.log'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn($request) =>
            $request->is('api/*') || $request->wantsJson()
        );
    })
    ->create();
