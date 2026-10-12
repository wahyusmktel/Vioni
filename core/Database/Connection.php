<?php

declare(strict_types=1);

namespace Vioni\Database;

use PDO;
use RuntimeException;

/**
 * Manages PDO Database Connections with hardened cyber security configurations:
 * - Emulated prepares DISABLED (Enforces native DB prepared statements)
 * - Strict exception error modes
 * - Secure utf8mb4 encoding
 */
class Connection
{
    private static ?PDO $pdo = null;

    /**
     * Get or initialize the active PDO connection.
     *
     * @param array<string, mixed>|null $customConfig
     */
    public static function getPdo(?array $customConfig = null): PDO
    {
        if (self::$pdo !== null && $customConfig === null) {
            return self::$pdo;
        }

        $config = $customConfig ?? self::loadConfig();
        $pdo = self::createConnection($config);

        if ($customConfig === null) {
            self::$pdo = $pdo;
        }

        return $pdo;
    }

    /**
     * Set a custom PDO instance (useful for testing with in-memory SQLite).
     */
    public static function setPdo(?PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * Load configuration from config/database.php.
     *
     * @return array<string, mixed>
     */
    private static function loadConfig(): array
    {
        $configFile = dirname(__DIR__, 2) . '/config/database.php';
        if (!file_exists($configFile)) {
            throw new RuntimeException("Database configuration file 'config/database.php' not found.");
        }

        $allConfig = require $configFile;
        $default = $allConfig['default'] ?? 'sqlite';

        return $allConfig['connections'][$default] ?? [];
    }

    /**
     * Instantiate and harden a PDO connection.
     *
     * @param array<string, mixed> $config
     */
    public static function createConnection(array $config): PDO
    {
        $driver = $config['driver'] ?? 'sqlite';

        $dsn = match ($driver) {
            'sqlite' => self::buildSqliteDsn($config),
            'mysql' => self::buildMysqlDsn($config),
            default => throw new RuntimeException("Unsupported database driver: '{$driver}'"),
        };

        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;

        // Hardened PDO Security Options
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // SECURITY CRITICAL: Disable emulated prepares to enforce native engine prepared statements
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function buildSqliteDsn(array $config): string
    {
        $database = $config['database'] ?? ':memory:';

        if ($database !== ':memory:') {
            $dir = dirname($database);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (!file_exists($database)) {
                touch($database);
            }
        }

        return "sqlite:{$database}";
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function buildMysqlDsn(array $config): string
    {
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $database = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        return "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
    }
}
