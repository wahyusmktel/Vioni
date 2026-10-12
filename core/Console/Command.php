<?php

declare(strict_types=1);

namespace Vioni\Console;

/**
 * Base abstract class for all Vioni CLI Commands.
 * Provides clean terminal formatting, colored text, tables, and argument parsing.
 */
abstract class Command
{
    protected string $signature = '';
    protected string $description = '';

    /**
     * Execute the command.
     *
     * @param array<string, mixed> $args
     * @param array<string, mixed> $options
     * @return int Exit code (0 for success, 1+ for failure)
     */
    abstract public function handle(array $args = [], array $options = []): int;

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Print colored output to terminal.
     */
    protected function line(string $text, ?string $color = null): void
    {
        $colors = [
            'green' => "\033[32m",
            'red' => "\033[31m",
            'yellow' => "\033[33m",
            'blue' => "\033[34m",
            'cyan' => "\033[36m",
            'magenta' => "\033[35m",
            'bold' => "\033[1m",
            'dim' => "\033[2m",
        ];

        $reset = "\033[0m";

        if ($color !== null && isset($colors[$color])) {
            echo $colors[$color] . $text . $reset . PHP_EOL;
        } else {
            echo $text . PHP_EOL;
        }
    }

    protected function info(string $text): void
    {
        $this->line("ℹ️  {$text}", 'cyan');
    }

    protected function success(string $text): void
    {
        $this->line("✅ {$text}", 'green');
    }

    protected function warning(string $text): void
    {
        $this->line("⚠️  {$text}", 'yellow');
    }

    protected function error(string $text): void
    {
        $this->line("❌ {$text}", 'red');
    }

    /**
     * Render an ASCII table in terminal.
     *
     * @param array<int, string> $headers
     * @param array<int, array<int, string>> $rows
     */
    protected function table(array $headers, array $rows): void
    {
        $colWidths = [];

        foreach ($headers as $i => $header) {
            $colWidths[$i] = mb_strlen($header);
        }

        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $cellLen = mb_strlen((string) $cell);
                if (!isset($colWidths[$i]) || $cellLen > $colWidths[$i]) {
                    $colWidths[$i] = $cellLen;
                }
            }
        }

        // Draw separator
        $drawSeparator = function () use ($colWidths): string {
            $line = '+';
            foreach ($colWidths as $width) {
                $line .= str_repeat('-', $width + 2) . '+';
            }
            return $line;
        };

        echo $drawSeparator() . PHP_EOL;

        // Draw header
        echo '|';
        foreach ($headers as $i => $header) {
            echo ' ' . str_pad($header, $colWidths[$i]) . ' |';
        }
        echo PHP_EOL;

        echo $drawSeparator() . PHP_EOL;

        // Draw rows
        foreach ($rows as $row) {
            echo '|';
            foreach ($headers as $i => $header) {
                $val = (string) ($row[$i] ?? '');
                echo ' ' . str_pad($val, $colWidths[$i]) . ' |';
            }
            echo PHP_EOL;
        }

        echo $drawSeparator() . PHP_EOL;
    }
}
