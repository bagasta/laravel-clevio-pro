<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\PgArray;

class Agent extends Model
{
    protected $table = 'Agent'; // ganti jika tabelmu bukan ini
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $casts = [
        'tools'             => PgArray::class,
        'memoryEnabled'     => 'boolean',
        'maxIterations'     => 'integer',
        'maxExecutionTime'  => 'float',
        'createdAt'         => 'datetime',
    ];

    protected $fillable = [
        'id','ownerId','name','modelName','systemMessage','tools',
        'memoryEnabled','memoryBackend','agentType','maxIterations',
        'maxExecutionTime','createdAt'
    ];
}
