<?php

declare(strict_types=1);

namespace Vioni\Console\Commands;

use Vioni\Console\Command;

class KeyGenerateCommand extends Command
{
    protected string $signature = 'key:generate';
    protected string $description = 'Generate a secure 256-bit cryptographic application key';

    public function handle(array $args = [], array $options = []): int
    {
        // 32 cryptographically secure random bytes (256-bit entropy)
        $bytes = random_bytes(32);
        $key = 'base64:' . base64_encode($bytes);

        $envFile = dirname(__DIR__, 3) . '/.env';
        $envExample = dirname(__DIR__, 3) . '/.env.example';

        if (!file_exists($envFile)) {
            if (file_exists($envExample)) {
                copy($envExample, $envFile);
            } else {
                file_put_contents($envFile, "APP_NAME=Vioni\nAPP_ENV=development\nAPP_KEY=\n");
            }
        }

        $content = file_get_contents($envFile) ?: '';
        if (preg_match('/^APP_KEY=.*$/m', $content)) {
            $content = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$key}", $content);
        } else {
            $content .= "\nAPP_KEY={$key}\n";
        }

        file_put_contents($envFile, $content);

        $this->success("Application key set successfully!");
        $this->line("Key: {$key}", 'cyan');

        return 0;
    }
}
