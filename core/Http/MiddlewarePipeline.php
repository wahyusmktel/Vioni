<?php

declare(strict_types=1);

namespace Vioni\Http;

use Closure;

/**
 * Executes an HTTP request through an onion-style middleware pipeline.
 */
class MiddlewarePipeline
{
    /** @var array<int, class-string<MiddlewareInterface>|MiddlewareInterface> */
    private array $middlewares = [];

    /**
     * @param array<int, class-string<MiddlewareInterface>|MiddlewareInterface> $middlewares
     */
    public function __construct(array $middlewares = [])
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Send request through the pipeline to the final destination handler.
     *
     * @param Request $request
     * @param Closure(Request): Response $destination
     * @return Response
     */
    public function send(Request $request, Closure $destination): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            function (Closure $next, string|MiddlewareInterface $middleware) {
                return function (Request $request) use ($next, $middleware): Response {
                    $instance = is_string($middleware) ? new $middleware() : $middleware;

                    if (!$instance instanceof MiddlewareInterface) {
                        throw new \RuntimeException("Class " . get_class($instance) . " must implement " . MiddlewareInterface::class);
                    }

                    return $instance->handle($request, $next);
                };
            },
            $destination
        );

        return $pipeline($request);
    }
}
