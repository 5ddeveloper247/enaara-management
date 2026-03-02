<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolePrivilege extends Model
{
    use SoftDeletes;

    protected $table = 'role_privileges';

    protected $fillable = [
        'role_id',
        'module_id',
    ];

    protected $casts = [
        'role_id' => 'integer',
        'module_id' => 'integer',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
