<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleCategory extends Model
{
    use SoftDeletes;

    protected $table = 'module_categories';

    protected $primaryKey = 'ID';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'category_name',
        'css_class',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class, 'module_category_id', 'ID');
    }
}
