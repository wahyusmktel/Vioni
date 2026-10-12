<?php

declare(strict_types=1);

namespace Vioni\Database;

use JsonSerializable;
use RuntimeException;

/**
 * Base Active Record Model for Vioni Framework.
 * Lightweight, type-safe, with built-in Mass Assignment Protection.
 */
abstract class Model implements JsonSerializable
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    /**
     * Allowed mass-assignable attributes (Cyber Security Defense against Over-Posting).
     *
     * @var array<int, string>
     */
    protected static array $fillable = [];

    /**
     * Internal model attribute values.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Track if this record already exists in the database.
     */
    protected bool $exists = false;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [], bool $exists = false)
    {
        $this->exists = $exists;
        if ($exists) {
            $this->attributes = $attributes;
        } else {
            $this->fill($attributes);
        }
    }

    /**
     * Get the associated table name (auto-infers from class name if empty).
     */
    public static function getTable(): string
    {
        if (static::$table !== '') {
            return static::$table;
        }

        // Infer table from class basename (e.g., App\Models\User -> users)
        $short = (new \ReflectionClass(static::class))->getShortName();
        return strtolower($short) . 's';
    }

    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }

    /**
     * Fill attributes with mass-assignment safety check.
     *
     * @param array<string, mixed> $attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            // Mass assignment defense
            if (empty(static::$fillable) || in_array($key, static::$fillable, true)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Start a QueryBuilder scoped to this Model.
     */
    public static function query(): QueryBuilder
    {
        return DB::table(static::getTable());
    }

    /**
     * Fetch all records as Model instances.
     *
     * @return array<int, static>
     */
    public static function all(): array
    {
        $rows = static::query()->get();
        return array_map(fn($row) => new static($row, true), $rows);
    }

    /**
     * Find a record by primary key.
     */
    public static function find(int|string $id): ?static
    {
        $row = static::query()->find($id, static::getPrimaryKey());
        if ($row === null) {
            return null;
        }
        return new static($row, true);
    }

    /**
     * Find a record by primary key or throw an exception.
     */
    public static function findOrFail(int|string $id): static
    {
        $model = static::find($id);
        if ($model === null) {
            throw new RuntimeException("Model not found with primary key '{$id}' on table '" . static::getTable() . "'");
        }
        return $model;
    }

    /**
     * Fluent where condition returning a QueryBuilder.
     */
    public static function where(string $column, mixed $operator = '=', mixed $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    /**
     * Create and immediately save a new record.
     *
     * @param array<string, mixed> $attributes
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Save the model (inserts if new, updates if exists).
     */
    public function save(): bool
    {
        $table = static::getTable();
        $pk = static::getPrimaryKey();

        if ($this->exists) {
            // Update
            $id = $this->attributes[$pk] ?? null;
            if ($id === null) {
                throw new RuntimeException("Cannot update record without primary key '{$pk}'.");
            }
            $updateData = $this->attributes;
            unset($updateData[$pk]);

            if (empty($updateData)) {
                return true;
            }

            DB::table($table)->where($pk, '=', $id)->update($updateData);
            return true;
        }

        // Insert
        $insertId = DB::table($table)->insert($this->attributes);
        if ($insertId) {
            $this->attributes[$pk] = is_numeric($insertId) ? (int) $insertId : $insertId;
        }
        $this->exists = true;

        return true;
    }

    /**
     * Delete this model record from the database.
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $pk = static::getPrimaryKey();
        $id = $this->attributes[$pk] ?? null;

        if ($id === null) {
            return false;
        }

        DB::table(static::getTable())->where($pk, '=', $id)->delete();
        $this->exists = false;

        return true;
    }

    /**
     * Convert attributes to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * JSON serialization support.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
