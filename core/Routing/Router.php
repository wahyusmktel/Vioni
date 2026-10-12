<?php

declare(strict_types=1);

namespace Vioni\Routing;

use Closure;
use ReflectionFunction;
use ReflectionMethod;
use Vioni\Http\Request;
use Vioni\Http\Response;

/**
 * Route collection and dispatcher engine.
 */
class Router
{
    private static ?self $instance = null;

    /** @var array<int, Route> */
    private array $routes = [];

    /** @var array<int, class-string<\Vioni\Http\MiddlewareInterface>|\Vioni\Http\MiddlewareInterface> */
    private array $globalMiddlewares = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a global middleware that applies to all routes.
     *
     * @param class-string<\Vioni\Http\MiddlewareInterface>|\Vioni\Http\MiddlewareInterface|array<int, class-string<\Vioni\Http\MiddlewareInterface>|\Vioni\Http\MiddlewareInterface> $middleware
     */
    public function middleware(string|object|array $middleware): self
    {
        if (is_array($middleware)) {
            $this->globalMiddlewares = array_merge($this->globalMiddlewares, $middleware);
        } else {
            $this->globalMiddlewares[] = $middleware;
        }
        return $this;
    }

    /**
     * Register a GET route.
     *
     * @param Closure|array{0: class-string, 1: string} $action
     */
    public function addRoute(string $method, string $uri, Closure|array $action): Route
    {
        $route = new Route(strtoupper($method), $uri, $action);
        $this->routes[] = $route;
        return $route;
    }

    /**
     * Dispatch an incoming request to matching route.
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        $params = [];
        $matchedRoute = null;
        $pathMatchesOtherMethod = false;

        foreach ($this->routes as $route) {
            if ($route->matches($method, $path, $params)) {
                $matchedRoute = $route;
                break;
            }

            if ($route->matchesPath($path)) {
                $pathMatchesOtherMethod = true;
            }
        }

        // 405 Method Not Allowed
        if ($matchedRoute === null && $pathMatchesOtherMethod) {
            return Response::json([
                'error' => 'Method Not Allowed',
                'message' => "The {$method} method is not allowed for path '{$path}'.",
            ], 405);
        }

        // 404 Not Found
        if ($matchedRoute === null) {
            return Response::json([
                'error' => 'Not Found',
                'message' => "Route '{$path}' was not found on this server.",
            ], 404);
        }

        // Collect all middlewares (Global + Route-specific)
        $middlewares = array_merge($this->globalMiddlewares, $matchedRoute->getMiddlewares());

        // Execute action through the Middleware Pipeline
        $pipeline = new \Vioni\Http\MiddlewarePipeline($middlewares);

        return $pipeline->send($request, function (Request $req) use ($matchedRoute, $params): Response {
            $result = $this->executeAction($matchedRoute->getAction(), $req, $params);
            return Response::make($result);
        });
    }

    /**
     * Execute route action with smart parameter injection.
     *
     * @param Closure|array{0: class-string, 1: string} $action
     * @param array<string, string> $routeParams
     */
    private function executeAction(Closure|array $action, Request $request, array $routeParams): mixed
    {
        if ($action instanceof Closure) {
            $reflector = new ReflectionFunction($action);
            $args = $this->resolveArguments($reflector->getParameters(), $request, $routeParams);
            return $action(...$args);
        }

        [$controllerClass, $method] = $action;

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller class '{$controllerClass}' does not exist.");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method '{$method}' does not exist on controller '{$controllerClass}'.");
        }

        $reflector = new ReflectionMethod($controller, $method);
        $args = $this->resolveArguments($reflector->getParameters(), $request, $routeParams);

        return $controller->$method(...$args);
    }

    /**
     * Match method arguments against Request instance or route parameters by name/type.
     *
     * @param array<\ReflectionParameter> $parameters
     * @param array<string, string> $routeParams
     * @return array<int, mixed>
     */
    private function resolveArguments(array $parameters, Request $request, array $routeParams): array
    {
        $args = [];

        foreach ($parameters as $param) {
            $type = $param->getType();
            $paramName = $param->getName();

            // Inject Request if type-hinted
            if ($type instanceof \ReflectionNamedType && $type->getName() === Request::class) {
                $args[] = $request;
                continue;
            }

            // Inject route parameter if available
            if (array_key_exists($paramName, $routeParams)) {
                $val = $routeParams[$paramName];

                // Auto-cast typed parameters (int, float, bool, string)
                if ($type instanceof \ReflectionNamedType) {
                    $args[] = match ($type->getName()) {
                        'int' => (int) $val,
                        'float' => (float) $val,
                        'bool' => (bool) $val,
                        default => $val,
                    };
                } else {
                    $args[] = $val;
                }
                continue;
            }

            // Default value if present
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            $args[] = null;
        }

        return $args;
    }

    /**
     * Clear all routes (useful for testing).
     */
    public function clear(): void
    {
        $this->routes = [];
    }

    /**
     * @return array<int, Route>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
