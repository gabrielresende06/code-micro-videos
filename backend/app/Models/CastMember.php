<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CastMember
 * @package App\Models
 *
 */
class CastMember extends Model {
    use SoftDeletes, UsesUuid;

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;

    protected $fillable = ['name', 'type'];

    protected $dates = ['deleted_at'];

    protected $casts = [];

    public static function getCastMembers() {
        return [self::TYPE_DIRECTOR, self::TYPE_ACTOR];
    }
}
