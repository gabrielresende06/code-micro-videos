<?php

namespace App\Models\Traits;

use Ramsey\Uuid\Uuid;

trait UsesUuid {

    protected static function bootUsesUuid() {
        static::creating(function ($model) {
            $model->id = Uuid::uuid4();
        });
    }

    public function getIncrementing() {
        return false;
    }

    public function getKeyType() {
        return 'string';
    }
}
