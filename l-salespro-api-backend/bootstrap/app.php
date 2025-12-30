<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.credit.limit' => \App\Http\Middleware\CheckCreditLimit::class,
            'log.api.activity'   => \App\Http\Middleware\LogApiActivity::class,
        ]);

        // Prevent any redirect attempt for unauthenticated users
        $middleware->redirectGuestsTo(fn (Request $request) => null);

        $middleware->group('api', [
            'log.api.activity',
            \App\Http\Middleware\ForceJsonResponse::class,  
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // 401 - No token / invalid token / expired
        $exceptions->render(function (AuthenticationException $e, Request $request): JsonResponse {
            return response()->json([
                'success'    => false,
                'message'    => 'Authentication required. Please provide a valid Bearer token.',
                'error_code' => 'unauthenticated',
                'status'     => 401,
            ], 401);
        });

        // 422 - Validation 
        $exceptions->render(function (ValidationException $e, Request $request): JsonResponse {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors'  => $e->errors(),  
                'status'  => 422,
            ], 422);
        });

        $exceptions->render(function (\Throwable $e, Request $request): JsonResponse {
            $response = [
                'success' => false,
                'message' => 'An internal server error occurred. Please try again later.',
                'status'  => 500,
            ];

            if (app()->environment('local', 'testing')) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile() . ':' . $e->getLine(),
                ];
            }

            return response()->json($response, 500);
        });

    })->create();