<?php

declare(strict_types=1);

namespace Vioni\Database;

use PDO;
use Throwable;

/**
 * Static Database Gateway for fluent querying and transaction management.
 */
class DB
{
    /**
     * Start a fluent query against the specified table.
     */
    public static function table(string $table): QueryBuilder
    {
        return new QueryBuilder($table);
    }

    /**
     * Execute a raw parameterized SELECT statement.
     *
     * @param array<int, mixed> $bindings
     * @return array<int, array<string, mixed>>
     */
    public static function select(string $query, array $bindings = []): array
    {
        $stmt = Connection::getPdo()->prepare($query);
        $stmt->execute($bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute a raw parameterized statement (INSERT, UPDATE, DELETE, DDL).
     *
     * @param array<int, mixed> $bindings
     */
    public static function statement(string $query, array $bindings = []): bool
    {
        $stmt = Connection::getPdo()->prepare($query);
        return $stmt->execute($bindings);
    }

    /**
     * Execute a callback within an ACID database transaction.
     * Automatically rolls back on any exception/error.
     *
     * @template T
     * @param callable(PDO): T $callback
     * @return T
     * @throws Throwable
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = Connection::getPdo();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getPdo(): PDO
    {
        return Connection::getPdo();
    }
}
