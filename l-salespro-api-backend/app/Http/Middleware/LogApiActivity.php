<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogApiActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        // Capture start time at the very beginning
        $startTime = microtime(true);

        // Execute the rest of the middleware stack and controller
        $response = $next($request);

        // Only log API requests
        if (!$request->is('api/*')) {
            return $response;
        }

        $user = Auth::user();
        $userId = $user?->id;

        // Prepare log data
        $logData = [
            'user_id'       => $userId,
            'action'        => $request->method() . ' ' . $request->path(),
            'subject_type'  => null, 
            'subject_id'    => null,
            'properties'    => json_encode([
                'method'          => $request->method(),
                'url'             => $request->fullUrl(),
                'ip_address'      => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'payload'         => $request->except(['password', 'password_confirmation', 'token', 'current_password']),
                'response_status' => $response->getStatusCode(),
                'response_time_ms'=> round((microtime(true) - $startTime) * 1000, 2),
            ]),
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
        ];

        try {
            ActivityLog::create($logData);
        } catch (\Exception $e) {
            // Prevent logging failure from crashing the response
            Log::error('Failed to create API activity log', [
                'error'   => $e->getMessage(),
                'data'    => $logData,
            ]);
        }

        return $response;
    }
}