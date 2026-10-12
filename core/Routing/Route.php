<?php

declare(strict_types=1);

namespace Vioni\Routing;

use Closure;

/**
 * Represents an individual registered HTTP route with fluent static registration helpers.
 */
class Route
{
    private string $regex = '';
    /** @var array<int, string> */
    private array $parameterNames = [];

    /**
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $uri Route URI pattern (e.g. /users/{id})
     * @param Closure|array{0: class-string, 1: string} $action Callback or [Controller, method]
     */
    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly Closure|array $action
    ) {
        $this->compileRegex();
    }

    /**
     * Register a GET route.
     *
     * @param Closure|array{0: class-string, 1: string} $action
     */
    public static function get(string $uri, Closure|array $action): self
    {
        return Router::getInstance()->addRoute('GET', $uri, $action);
    }

    /**
     * Register a POST route.
     *
     * @param Closure|array{0: class-string, 1: string} $action
     */
    public static function post(string $uri, Closure|array $action): self
    {
        return Router::getInstance()->addRoute('POST', $uri, $action);
    }

    /**
     * Register a PUT route.
     *
     * @param Closure|array{0: class-string, 1: string} $action
     */
    public static function put(string $uri, Closure|array $action): self
    {
        return Router::getInstance()->addRoute('PUT', $uri, $action);
    }

    /**
     * Register a DELETE route.
     *
     * @param Closure|array{0: class-string, 1: string} $action
     */
    public static function delete(string $uri, Closure|array $action): self
    {
        return Router::getInstance()->addRoute('DELETE', $uri, $action);
    }

    /**
     * Register a PATCH route.
     *
     * @param Closure|array{0: class-string, 1: string} $action
     */
    public static function patch(string $uri, Closure|array $action): self
    {
        return Router::getInstance()->addRoute('PATCH', $uri, $action);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return Closure|array{0: class-string, 1: string}
     */
    public function getAction(): Closure|array
    {
        return $this->action;
    }

    /**
     * Check if this route matches the given method and path, and extract parameters.
     *
     * @param string $method
     * @param string $path
     * @param array<string, string> $params Out-parameter containing extracted route params
     * @return bool
     */
    public function matches(string $method, string $path, array &$params = []): bool
    {
        if ($this->method !== $method) {
            return false;
        }

        if (preg_match($this->regex, $path, $matches)) {
            $extracted = [];
            foreach ($this->parameterNames as $name) {
                if (isset($matches[$name])) {
                    $extracted[$name] = $matches[$name];
                }
            }
            $params = $extracted;
            return true;
        }

        return false;
    }

    /**
     * Check if path matches regardless of HTTP method (for 405 Method Not Allowed detection).
     */
    public function matchesPath(string $path): bool
    {
        return (bool) preg_match($this->regex, $path);
    }

    /**
     * Compile URI pattern like '/users/{id}/posts/{slug}' into a regex.
     */
    private function compileRegex(): void
    {
        $normalized = '/' . trim($this->uri, '/');
        if ($normalized !== '/') {
            $normalized = rtrim($normalized, '/');
        }

        $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) {
            $this->parameterNames[] = $matches[1];
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $normalized);

        $this->regex = '#^' . $pattern . '$#';
    }
}
