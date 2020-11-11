<?php

namespace Tests\Feature\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CategoryTest extends TestCase {

    use DatabaseMigrations;

    public function testList() {
        factory(Category::class)->create();

        $categories = Category::all();
        $this->assertCount(1, $categories);
        $categoryKeys = array_keys( $categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $categoryKeys);
    }

    public function testCreate() {
        $category = Category::create([
            'name' => 'test1',
        ]);
        $category->refresh();
        $this->assertTrue(Uuid::isValid($category->id));
        $this->assertEquals('test1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);
        $category = Category::create([
            'name' => 'test1',
            'description' => null,
        ]);
        $category->refresh();
        $this->assertNull($category->description);

        $category = Category::create([
            'name' => 'test1',
            'description' => 'test_description',
        ]);
        $category->refresh();
        $this->assertEquals('test_description', $category->description);

        $category = Category::create([
            'name' => 'test1',
            'description' => 'test_description',
            'is_active' => false
        ]);
        $category->refresh();
        $this->assertFalse( $category->is_active);

        $category = Category::create([
            'name' => 'test1',
            'description' => 'test_description',
            'is_active' => true
        ]);
        $category->refresh();
        $this->assertTrue( $category->is_active);
    }

    public function testUpdate() {
        $category = factory(Category::class)->create([
            'description' => 'test_description',
            'is_active' => false
        ]);

        $category->update([
            'name' => 'test_name_updated',
            'description' => 'test_description_updated',
            'is_active' => true
        ]);

        $this->assertTrue(Uuid::isValid($category->id));
        $this->assertEquals('test_name_updated', $category->name);
        $this->assertEquals('test_description_updated', $category->description);
        $this->assertTrue($category->is_active);
    }

    public function testDelete() {
        $category = factory(Category::class)->create();

        $categories = Category::all();
        $this->assertCount(1, $categories);
        $category->delete();

        $categoriesAfterDelete = Category::all();
        $this->assertCount(0, $categoriesAfterDelete);
    }
}
