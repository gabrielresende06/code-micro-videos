<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;

class CastMemberController extends BasicCrudController {

    private $rules;

    public function __construct() {
        $this->rules = [
            'name' => 'required|max:255',
            'type' => 'required|in:' . implode(',', CastMember::getCastMembers()),
        ];
    }

    protected function model() {
        return \App\Models\CastMember::class;
    }

    protected function rulesStore() {
        return $this->rules;
    }

    protected function rulesUpdate() {
        return $this->rules;
    }

    protected function resource() {
        return CastMemberResource::class;
    }

    protected function resourceCollection() {
        return $this->resource();
    }
}
