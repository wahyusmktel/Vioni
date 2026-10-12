<?php

declare(strict_types=1);

namespace Vioni\Console;

use Vioni\Console\Commands\KeyGenerateCommand;
use Vioni\Console\Commands\MakeControllerCommand;
use Vioni\Console\Commands\MakeMiddlewareCommand;
use Vioni\Console\Commands\MakeModelCommand;
use Vioni\Console\Commands\RouteListCommand;
use Vioni\Console\Commands\SecurityAuditCommand;
use Vioni\Console\Commands\ServeCommand;
use Vioni\Console\Commands\TestCommand;
use Vioni\Foundation\Application;

class Kernel
{
    /** @var array<string, Command> */
    private array $commands = [];

    public function __construct(
        private readonly ?Application $app = null
    ) {
        $this->registerDefaultCommands();
    }

    private function registerDefaultCommands(): void
    {
        $this->register(new ServeCommand());
        $this->register(new RouteListCommand());
        $this->register(new KeyGenerateCommand());
        $this->register(new SecurityAuditCommand());
        $this->register(new MakeControllerCommand());
        $this->register(new MakeMiddlewareCommand());
        $this->register(new MakeModelCommand());
        $this->register(new TestCommand());
    }

    public function register(Command $command): self
    {
        $this->commands[$command->getSignature()] = $command;
        return $this;
    }

    /**
     * Handle incoming command line arguments.
     *
     * @param array<int, string> $argv
     */
    public function handle(array $argv): int
    {
        // Strip script name ($argv[0])
        array_shift($argv);

        $commandName = array_shift($argv) ?? 'help';

        if ($commandName === 'help' || $commandName === '--help' || $commandName === '-h') {
            $this->printHelp();
            return 0;
        }

        if (!isset($this->commands[$commandName])) {
            echo "\033[31m❌ Command '{$commandName}' is not defined.\033[0m" . PHP_EOL;
            echo "Run \033[33mphp vioni help\033[0m to see available commands." . PHP_EOL;
            return 1;
        }

        $command = $this->commands[$commandName];

        // Parse arguments & options
        $args = [];
        $options = [];

        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--')) {
                $opt = substr($arg, 2);
                if (str_contains($opt, '=')) {
                    [$k, $v] = explode('=', $opt, 2);
                    $options[$k] = $v;
                } else {
                    $options[$opt] = true;
                }
            } else {
                $args[] = $arg;
            }
        }

        return $command->handle($args, $options);
    }

    /**
     * Display help banner and available commands list.
     */
    public function printHelp(): void
    {
        echo <<<BANNER
\033[36m
  __     ___             _   
  \ \   / (_)           (_)  
   \ \ / / _  ___  _ __  _   
    \ V / | |/ _ \| '_ \| |  
     \ /  | | (_) | | | | |  
      V   |_|\___/|_| |_|_|  
\033[0m
\033[1mVioni Framework CLI\033[0m \033[2m(PHP 8.4+ Engine)\033[0m

\033[33mUsage:\033[0m
  php vioni <command> [arguments] [options]

\033[33mAvailable Commands:\033[0m

BANNER;

        $grouped = [];
        foreach ($this->commands as $name => $cmd) {
            $parts = explode(':', $name);
            $group = count($parts) > 1 ? $parts[0] : 'general';
            $grouped[$group][$name] = $cmd->getDescription();
        }

        foreach ($grouped as $group => $commands) {
            echo "\033[34m " . ucfirst($group) . "\033[0m" . PHP_EOL;
            foreach ($commands as $name => $desc) {
                echo "  \033[32m" . str_pad($name, 22) . "\033[0m " . $desc . PHP_EOL;
            }
            echo PHP_EOL;
        }
    }
}
