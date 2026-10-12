<?php

declare(strict_types=1);

namespace Vioni\Database;

use InvalidArgumentException;
use PDO;

/**
 * Fluent, Type-Safe, and Cyber-Secure Database Query Builder.
 * Defends against SQL Injections using:
 * - 100% Parameterized PDO bindings
 * - Strict Identifier Whitelisting (table & column validation)
 * - Strict SQL Operator Whitelisting
 */
class QueryBuilder
{
    private string $table;
    /** @var array<int, string> */
    private array $columns = ['*'];
    /** @var array<int, array{column: string, operator: string, value: mixed, boolean: string}> */
    private array $wheres = [];
    /** @var array<int, array{column: string, direction: string}> */
    private array $orders = [];
    private ?int $limitValue = null;
    private ?int $offsetValue = null;
    /** @var array<int, mixed> */
    private array $bindings = [];

    private const ALLOWED_OPERATORS = [
        '=', '!=', '<>', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'ILIKE',
    ];

    public function __construct(string $table, private readonly ?PDO $pdo = null)
    {
        $this->table = $this->sanitizeIdentifier($table);
    }

    private function getPdo(): PDO
    {
        return $this->pdo ?? Connection::getPdo();
    }

    /**
     * Validate and sanitize table/column names against injection vectors.
     */
    private function sanitizeIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);

        if ($identifier === '*') {
            return '*';
        }

        // Support table.column alias (e.g., users.id, users.name as user_name)
        if (preg_match('/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)?(\s+as\s+[a-zA-Z0-9_]+)?$/i', $identifier)) {
            return $identifier;
        }

        throw new InvalidArgumentException("Potential SQL Injection detected: Invalid identifier name '{$identifier}'");
    }

    /**
     * Set columns to select.
     *
     * @param array<int, string>|string $columns
     */
    public function select(array|string $columns = ['*']): self
    {
        $cols = is_array($columns) ? $columns : func_get_args();
        $this->columns = array_map([$this, 'sanitizeIdentifier'], $cols);
        return $this;
    }

    /**
     * Add a WHERE condition with strict operator validation.
     */
    public function where(string $column, mixed $operator = '=', mixed $value = null, string $boolean = 'AND'): self
    {
        // Shorthand syntax: where('name', 'John') => where('name', '=', 'John')
        if ($value === null && func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $sanitizedCol = $this->sanitizeIdentifier($column);
        $operator = strtoupper(trim((string) $operator));

        if (!in_array($operator, self::ALLOWED_OPERATORS, true)) {
            throw new InvalidArgumentException("Invalid or disallowed SQL operator: '{$operator}'");
        }

        $this->wheres[] = [
            'column' => $sanitizedCol,
            'operator' => $operator,
            'value' => $value,
            'boolean' => strtoupper($boolean),
        ];

        return $this;
    }

    /**
     * Add an OR WHERE condition.
     */
    public function orWhere(string $column, mixed $operator = '=', mixed $value = null): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add a WHERE IN condition.
     *
     * @param array<int, mixed> $values
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        if (empty($values)) {
            return $this->where('1', '=', '0'); // Always false
        }

        $sanitizedCol = $this->sanitizeIdentifier($column);
        $this->wheres[] = [
            'column' => $sanitizedCol,
            'operator' => 'IN',
            'value' => array_values($values),
            'boolean' => strtoupper($boolean),
        ];

        return $this;
    }

    /**
     * Order results by column.
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $sanitizedCol = $this->sanitizeIdentifier($column);
        $direction = strtoupper(trim($direction));

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }

        $this->orders[] = [
            'column' => $sanitizedCol,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * Limit maximum number of results.
     */
    public function limit(int $limit): self
    {
        $this->limitValue = max(0, $limit);
        return $this;
    }

    /**
     * Offset the results.
     */
    public function offset(int $offset): self
    {
        $this->offsetValue = max(0, $offset);
        return $this;
    }

    /**
     * Compile the SELECT query into SQL and gather parameter bindings.
     *
     * @return array{0: string, 1: array<int, mixed>} [sql, bindings]
     */
    public function toSqlAndBindings(): array
    {
        $columns = implode(', ', $this->columns);
        $sql = "SELECT {$columns} FROM {$this->table}";
        $bindings = [];

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ';
            $whereParts = [];

            foreach ($this->wheres as $i => $where) {
                $prefix = $i > 0 ? "{$where['boolean']} " : '';

                if ($where['operator'] === 'IN') {
                    $placeholders = implode(', ', array_fill(0, count($where['value']), '?'));
                    $whereParts[] = "{$prefix}{$where['column']} IN ({$placeholders})";
                    foreach ($where['value'] as $val) {
                        $bindings[] = $val;
                    }
                } else {
                    $whereParts[] = "{$prefix}{$where['column']} {$where['operator']} ?";
                    $bindings[] = $where['value'];
                }
            }

            $sql .= implode(' ', $whereParts);
        }

        if (!empty($this->orders)) {
            $orderParts = [];
            foreach ($this->orders as $order) {
                $orderParts[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderParts);
        }

        if ($this->limitValue !== null) {
            $sql .= " LIMIT {$this->limitValue}";
        }

        if ($this->offsetValue !== null) {
            $sql .= " OFFSET {$this->offsetValue}";
        }

        return [$sql, $bindings];
    }

    /**
     * Fetch all matching records.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        [$sql, $bindings] = $this->toSqlAndBindings();
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch the first matching record.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Find a single record by primary key.
     *
     * @return array<string, mixed>|null
     */
    public function find(int|string $id, string $primaryKey = 'id'): ?array
    {
        return $this->where($primaryKey, '=', $id)->first();
    }

    /**
     * Count matching rows.
     */
    public function count(string $column = '*'): int
    {
        $sanitized = $column === '*' ? '*' : $this->sanitizeIdentifier($column);
        $this->columns = ["COUNT({$sanitized}) as aggregate"];
        $result = $this->first();

        return (int) ($result['aggregate'] ?? 0);
    }

    /**
     * Insert a new record safely using prepared statements.
     *
     * @param array<string, mixed> $data
     * @return string Last insert ID
     */
    public function insert(array $data): string
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Cannot insert empty data.");
        }

        $columns = [];
        $placeholders = [];
        $bindings = [];

        foreach ($data as $key => $val) {
            $columns[] = $this->sanitizeIdentifier($key);
            $placeholders[] = '?';
            $bindings[] = $val;
        }

        $colList = implode(', ', $columns);
        $placeList = implode(', ', $placeholders);

        $sql = "INSERT INTO {$this->table} ({$colList}) VALUES ({$placeList})";
        $pdo = $this->getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);

        return $pdo->lastInsertId();
    }

    /**
     * Update matching records safely using prepared statements.
     *
     * @param array<string, mixed> $data
     * @return int Affected rows count
     */
    public function update(array $data): int
    {
        if (empty($data)) {
            throw new InvalidArgumentException("Cannot update with empty data.");
        }

        $setParts = [];
        $bindings = [];

        foreach ($data as $key => $val) {
            $col = $this->sanitizeIdentifier($key);
            $setParts[] = "{$col} = ?";
            $bindings[] = $val;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ';
            $whereParts = [];

            foreach ($this->wheres as $i => $where) {
                $prefix = $i > 0 ? "{$where['boolean']} " : '';
                $whereParts[] = "{$prefix}{$where['column']} {$where['operator']} ?";
                $bindings[] = $where['value'];
            }

            $sql .= implode(' ', $whereParts);
        }

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->rowCount();
    }

    /**
     * Delete matching records safely using prepared statements.
     *
     * @return int Affected rows count
     */
    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";
        $bindings = [];

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ';
            $whereParts = [];

            foreach ($this->wheres as $i => $where) {
                $prefix = $i > 0 ? "{$where['boolean']} " : '';
                $whereParts[] = "{$prefix}{$where['column']} {$where['operator']} ?";
                $bindings[] = $where['value'];
            }

            $sql .= implode(' ', $whereParts);
        }

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->rowCount();
    }
}
