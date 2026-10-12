<?php

declare(strict_types=1);

namespace App\Models;

use Vioni\Database\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'id';

    /**
     * Allowed mass-assignable attributes (Cyber Security Defense against Over-Posting).
     *
     * @var array<int, string>
     */
    protected static array $fillable = [
        'name',
        'email',
        'status',
    ];
}
