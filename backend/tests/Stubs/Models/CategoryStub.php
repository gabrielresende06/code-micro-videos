<?php

namespace Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class CategoryStub extends Model {

    protected $fillable = ['name', 'description'];
    protected $table = 'category_stubs';

    public static function createTable() {
        self::dropTable();
        \Schema::create('category_stubs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public static function dropTable() {
        \Schema::dropIfExists('category_stubs');
    }
}
