<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\UsesUuid;

class Category extends Model {

    use SoftDeletes, UsesUuid;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $dates = ['deleted_at'];

    protected $casts = ['is_active' => 'boolean'];
}
