<?php

declare(strict_types=1);

namespace Vioni\Console\Commands;

use Vioni\Console\Command;

class MakeModelCommand extends Command
{
    protected string $signature = 'make:model';
    protected string $description = 'Create a new Vioni Database Model class';

    public function handle(array $args = [], array $options = []): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error("Please specify model name! Example: php vioni make:model User");
            return 1;
        }

        $className = str_replace(['/', '\\'], '', $name);
        $targetDir = dirname(__DIR__, 3) . '/app/Models';
        $targetFile = $targetDir . '/' . $className . '.php';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (file_exists($targetFile)) {
            $this->error("Model '{$className}' already exists at: {$targetFile}");
            return 1;
        }

        $tableName = strtolower($className) . 's';

        $template = <<<PHP
<?php

declare(strict_types=1);

namespace App\Models;

use Vioni\Database\Model;

class {$className} extends Model
{
    protected static string \$table = '{$tableName}';
    protected static string \$primaryKey = 'id';

    /**
     * Allowed mass-assignable attributes (Cyber Security Defense against Over-Posting).
     *
     * @var array<int, string>
     */
    protected static array \$fillable = [
        // 'name', 'email'
    ];
}

PHP;

        file_put_contents($targetFile, $template);

        $this->success("Model [app/Models/{$className}.php] created successfully.");

        return 0;
    }
}
