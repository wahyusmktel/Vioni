<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Vioni\Http\MiddlewareInterface;
use Vioni\Http\Request;
use Vioni\Http\Response;
use Vioni\Security\Csrf;

/**
 * Verifies CSRF Token on all state-changing requests (POST, PUT, DELETE, PATCH).
 */
class VerifyCsrfToken implements MiddlewareInterface
{
    /**
     * URIs that should be excluded from CSRF verification (e.g. external webhooks or API).
     *
     * @var array<int, string>
     */
    protected array $except = [
        '/api/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Read-only methods (GET, HEAD, OPTIONS) do not require CSRF token
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        // 2. Check if current URI is in the excluded list
        if ($this->isExcluded($request->path())) {
            return $next($request);
        }

        // 3. Extract token from POST payload or X-CSRF-TOKEN header
        $token = $request->input('_csrf') ?? $request->header('X-CSRF-TOKEN');

        if (!is_string($token) || !Csrf::validate($token)) {
            return Response::json([
                'error' => 'CSRF Token Mismatch',
                'message' => 'The provided CSRF token is invalid or has expired. Please refresh the page and try again.',
            ], 419);
        }

        return $next($request);
    }

    /**
     * Check if path matches exclusion patterns.
     */
    private function isExcluded(string $path): bool
    {
        foreach ($this->except as $pattern) {
            $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';
            if (preg_match($regex, $path)) {
                return true;
            }
        }

        return false;
    }
}
