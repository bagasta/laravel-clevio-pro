<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrismaUser extends Model
{
    // Prisma biasanya membuat tabel "User" (quoted, case-sensitive)
    protected $table = 'User';

    /**
     * Use the Postgres connection for Prisma tables.
     */
    protected $connection = 'pgsql';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'email', 'name', 'createdAt'];

    /**
     * Agents owned by this Prisma user.
     */
    public function agents()
    {
        return $this->hasMany(Agent::class, 'ownerId', 'id');
    }
}
