<?php

declare(strict_types=1);

namespace Vioni\Http;

use Closure;

/**
 * Interface that every Vioni middleware must implement.
 */
interface MiddlewareInterface
{
    /**
     * Handle an incoming HTTP request.
     *
     * @param Request $request
     * @param Closure(Request): Response $next Next middleware or controller handler
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response;
}
