<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrismaUser extends Model
{
    // Prisma biasanya membuat tabel "User" (quoted, case-sensitive)
    protected $table = 'User';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'email', 'name', 'createdAt'];
}
