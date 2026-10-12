<?php

declare(strict_types=1);

namespace Vioni\Foundation;

use Throwable;
use Vioni\Http\Request;
use Vioni\Http\Response;
use Vioni\Routing\Router;

/**
 * The Vioni Application Kernel.
 * Orchestrates the application lifecycle, route loading, and secure exception handling.
 */
class Application
{
    private static ?self $instance = null;
    private Router $router;
    /** @var array<string, mixed> */
    private array $config = [];

    public function __construct(
        private readonly string $basePath
    ) {
        self::$instance = $this;
        $this->router = Router::getInstance();
        $this->loadConfiguration();
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Load application configuration safely.
     */
    private function loadConfiguration(): void
    {
        $appConfig = $this->basePath . '/config/app.php';
        if (file_exists($appConfig)) {
            $this->config = require $appConfig;
        }
    }

    /**
     * Get a configuration value.
     */
    public function config(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Boot and load routes from standard route files.
     */
    public function boot(): self
    {
        $webRoutes = $this->basePath . '/routes/web.php';
        if (file_exists($webRoutes)) {
            require_once $webRoutes;
        }

        $apiRoutes = $this->basePath . '/routes/api.php';
        if (file_exists($apiRoutes)) {
            require_once $apiRoutes;
        }

        return $this;
    }

    /**
     * Handle incoming HTTP request and return HTTP response.
     */
    public function handle(Request $request): Response
    {
        try {
            return $this->router->dispatch($request);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Cybersecurity-conscious exception handler.
     * Prevents internal details leakage in production.
     */
    private function handleException(Throwable $e): Response
    {
        $isDebug = (bool) $this->config('debug', false);

        if ($isDebug) {
            return Response::json([
                'error' => 'Server Error',
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ], 500);
        }

        // Production-safe response (No information disclosure vulnerability)
        return Response::json([
            'error' => 'Internal Server Error',
            'message' => 'An unexpected error occurred. Please contact the administrator.',
        ], 500);
    }
}
