<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model {

    use SoftDeletes, UsesUuid;

    protected $fillable = ['name', 'is_active'];

    protected $dates = ['deleted_at'];

    protected $casts = ['is_active' => 'boolean', 'id' => 'string'];

    /**
     * @return BelongsToMany
     */
    public function categories() {
        return $this->belongsToMany(Category::class)->withTrashed();
    }
}
