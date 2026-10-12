<?php

declare(strict_types=1);

namespace Vioni\Console\Commands;

use Vioni\Console\Command;

class MakeMiddlewareCommand extends Command
{
    protected string $signature = 'make:middleware';
    protected string $description = 'Create a new Vioni HTTP middleware class';

    public function handle(array $args = [], array $options = []): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error("Please specify middleware name! Example: php vioni make:middleware EnsureAuth");
            return 1;
        }

        $className = str_replace(['/', '\\'], '', $name);
        $targetDir = dirname(__DIR__, 3) . '/app/Middleware';
        $targetFile = $targetDir . '/' . $className . '.php';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (file_exists($targetFile)) {
            $this->error("Middleware '{$className}' already exists at: {$targetFile}");
            return 1;
        }

        $template = <<<PHP
<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Vioni\Http\MiddlewareInterface;
use Vioni\Http\Request;
use Vioni\Http\Response;

class {$className} implements MiddlewareInterface
{
    /**
     * Handle incoming request before passing to controller.
     */
    public function handle(Request \$request, Closure \$next): Response
    {
        // 1. Perform pre-processing, security checks, or auth verification here

        // 2. Pass request to next layer
        \$response = \$next(\$request);

        // 3. Perform post-processing if needed

        return \$response;
    }
}

PHP;

        file_put_contents($targetFile, $template);

        $this->success("Middleware [app/Middleware/{$className}.php] created successfully.");

        return 0;
    }
}
