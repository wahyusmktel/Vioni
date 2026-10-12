<?php

declare(strict_types=1);

namespace Vioni\Console\Commands;

use Vioni\Console\Command;

class TestCommand extends Command
{
    protected string $signature = 'test';
    protected string $description = 'Run the automated test suite';

    public function handle(array $args = [], array $options = []): int
    {
        $testScript = dirname(__DIR__, 3) . '/tests/test_run.php';

        if (file_exists($testScript)) {
            $this->info("Running Vioni Native Test Runner...");
            passthru("php \"{$testScript}\"", $status);
            return $status;
        }

        $this->error("No test suite found in tests/ directory.");
        return 1;
    }
}
