<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CategoriesTest extends TestCase {

    use DatabaseMigrations;

    /** @test  */
    public function list_all_categories() {

        $categories = factory(Category::class, 100)->create();

        $this
            ->get('/api/categories', $this->headers)
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => $categories->first()->id
                ]
            ])
            ->assertJsonCount(100);
    }

    /** @test  */
    public function name_is_required_to_add_new_category() {
        $this->post('/api/categories', [], $this->headers)
            ->assertStatus(422)
            ->assertJsonFragment(
                ["The name field is required."]
            );

        $this->assertCount(0, Category::all());
    }

    /** @test  */
    public function can_add_new_category() {
        $data = [
            'name' => 'Category 1',
            'description' => 'Category description'
        ];

        $this->post('/api/categories', $data, $this->headers)
            ->assertStatus(201)
            ->assertJsonFragment(
                $data
            );

        $this->assertCount(1, Category::all());
    }

    /** @test  */
    public function can_edit_a_category() {
        $category = factory(Category::class)->create();

        $this->put('/api/categories/'. $category->id, [
            'name' => 'Updating category'
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => 'Updating category',
                    'id' => $category->id
                ]
            );

        $this->assertCount(1, Category::all());
    }

    /** @test  */
    public function can_delete_a_category() {
        $category = factory(Category::class)->create();

        $this->delete('/api/categories/'. $category->id, $this->headers)
            ->assertStatus(204)
            ->assertNoContent();

        $this->assertCount(0, Category::all());
    }
}
