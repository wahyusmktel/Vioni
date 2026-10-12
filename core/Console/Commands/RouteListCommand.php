<?php

declare(strict_types=1);

namespace Vioni\Console\Commands;

use Closure;
use Vioni\Console\Command;
use Vioni\Routing\Router;

class RouteListCommand extends Command
{
    protected string $signature = 'route:list';
    protected string $description = 'Display all registered HTTP routes';

    public function handle(array $args = [], array $options = []): int
    {
        $router = Router::getInstance();
        $routes = $router->getRoutes();

        if (empty($routes)) {
            $this->warning("No routes registered in this application.");
            return 0;
        }

        $this->line("📍 Registered Application Routes (" . count($routes) . " Total)", 'bold');

        $headers = ['Method', 'URI', 'Handler / Action'];
        $rows = [];

        foreach ($routes as $route) {
            $action = $route->getAction();
            $actionStr = '';

            if ($action instanceof Closure) {
                $ref = new \ReflectionFunction($action);
                $file = basename($ref->getFileName() ?: 'unknown');
                $line = $ref->getStartLine();
                $actionStr = "Closure ({$file}:{$line})";
            } elseif (is_array($action)) {
                $actionStr = "{$action[0]}@{$action[1]}";
            }

            $rows[] = [
                $route->getMethod(),
                $route->getUri(),
                $actionStr,
            ];
        }

        $this->table($headers, $rows);

        return 0;
    }
}
