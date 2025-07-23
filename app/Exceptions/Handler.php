<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Get the response first
        $response = parent::render($request, $exception);
        
        // Check if it's a 429 response (rate limited)
        if ($response->getStatusCode() === 429) {
            Log::channel('api-errors')->error('Too Many Requests - Rate limit exceeded', [
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'full_url' => $request->fullUrl(),
                'method' => $request->method(),
                'input' => $request->all(),
                'user_agent' => $request->userAgent(),
                'status_code' => $response->getStatusCode(),
                'timestamp' => now()->toISOString(),
            ]);
        }

        return $response;
    }
}