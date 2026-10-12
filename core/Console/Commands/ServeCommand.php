<?php

declare(strict_types=1);

namespace Vioni\Console\Commands;

use Vioni\Console\Command;

class ServeCommand extends Command
{
    protected string $signature = 'serve';
    protected string $description = 'Start the Vioni local development server';

    public function handle(array $args = [], array $options = []): int
    {
        $host = $options['host'] ?? '127.0.0.1';
        $port = (int) ($options['port'] ?? 8000);
        $publicDir = dirname(__DIR__, 3) . '/public';

        $this->line("==================================================", 'cyan');
        $this->line("   🚀 Vioni Development Server Started", 'bold');
        $this->line("==================================================", 'cyan');
        $this->info("Listening on: http://{$host}:{$port}");
        $this->line("Document root: {$publicDir}", 'dim');
        $this->line("Press Ctrl+C to terminate.", 'yellow');
        $this->line("");

        // Execute PHP built-in web server
        passthru("php -S {$host}:{$port} -t \"{$publicDir}\"", $status);

        return $status;
    }
}
