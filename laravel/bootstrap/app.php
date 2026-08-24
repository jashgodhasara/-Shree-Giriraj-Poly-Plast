<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'verify-gstin',
            'api/*',
        ]);
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\PrettyPrintJson::class,
        ]);
        $middleware->alias([
            'admin'     => \App\Http\Middleware\AdminMiddleware::class,
            'api.admin' => \App\Http\Middleware\ApiAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                $firstError = collect($e->errors())->flatten()->first();
                return response()->json([
                    'success' => false,
                    'message' => $firstError ?: 'Validation error.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            $isConstraint = str_contains($e->getMessage(), 'FOREIGN KEY constraint failed') 
                || str_contains($e->getMessage(), '1451') 
                || str_contains($e->getMessage(), 'Integrity constraint violation');

            if ($isConstraint) {
                $msg = 'Cannot delete or alter this record because it is referenced in existing transactions (such as Invoices, Ledger, Orders, or Stock Logs).';
                if ($request->is('api/*') || $request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 422);
                }
                return back()->with('error', $msg);
            }
        });
    })->create();
