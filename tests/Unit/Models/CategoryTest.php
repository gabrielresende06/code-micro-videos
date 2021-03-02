<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class CategoryTest extends TestCase {

    private $category;

    protected function setUp(): void {
        parent::setUp();
        $this->category = new Category();
    }

    public function testIfUseTraits() {
        $traits = [
            SoftDeletes::class,
            UsesUuid::class
        ];

        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);
    }

    public function testFillableAttributes() {
        $fillable = ['name', 'description', 'is_active'];
        $this->assertEquals($fillable, $this->category->getFillable());
    }

    public function testKeyType() {
        $this->assertEquals('string', $this->category->getKeyType());
    }

    public function testIncrementing() {
        $this->assertFalse($this->category->getIncrementing());
    }

    public function testDatesAttribute() {
        $dates = collect(['deleted_at', 'created_at', 'updated_at']);
        foreach ($dates as $date) {
            $this->assertContains($date, $this->category->getDates());
        }

        $this->assertCount($dates->count(), $this->category->getDates());
    }

    public function testCasts() {
        $casts = ['is_active' => 'boolean'];
        $this->assertEquals($casts, $this->category->getCasts());
    }
}
