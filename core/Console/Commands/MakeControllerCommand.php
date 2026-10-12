<?php

declare(strict_types=1);

namespace Vioni\Console\Commands;

use Vioni\Console\Command;

class MakeControllerCommand extends Command
{
    protected string $signature = 'make:controller';
    protected string $description = 'Create a new Vioni HTTP controller class';

    public function handle(array $args = [], array $options = []): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error("Please specify controller name! Example: php vioni make:controller UserController");
            return 1;
        }

        // Normalize class name and namespace
        $name = str_replace('/', '\\', $name);
        $parts = explode('\\', $name);
        $className = array_pop($parts);
        if (!str_ends_with($className, 'Controller')) {
            $className .= 'Controller';
        }

        $subNamespace = !empty($parts) ? '\\' . implode('\\', $parts) : '';
        $targetDir = dirname(__DIR__, 3) . '/app/Controllers/' . implode('/', $parts);
        $targetFile = $targetDir . '/' . $className . '.php';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (file_exists($targetFile)) {
            $this->error("Controller '{$className}' already exists at: {$targetFile}");
            return 1;
        }

        $isApi = isset($options['api']);

        $template = $isApi
            ? $this->getApiTemplate($subNamespace, $className)
            : $this->getStandardTemplate($subNamespace, $className);

        file_put_contents($targetFile, $template);

        $this->success("Controller [app/Controllers/" . (!empty($parts) ? implode('/', $parts) . '/' : '') . "{$className}.php] created successfully.");

        return 0;
    }

    private function getStandardTemplate(string $subNamespace, string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Controllers{$subNamespace};

use Vioni\Http\Request;

class {$className}
{
    public function index(Request \$request): string
    {
        return "Hello from {$className}!";
    }
}

PHP;
    }

    private function getApiTemplate(string $subNamespace, string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Controllers{$subNamespace};

use Vioni\Http\Request;

class {$className}
{
    public function index(Request \$request): array
    {
        return [
            'status' => 'success',
            'data' => [],
        ];
    }

    public function show(int \$id): array
    {
        return [
            'status' => 'success',
            'id' => \$id,
        ];
    }
}

PHP;
    }
}
