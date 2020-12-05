<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Lang;
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
    public function can_retrieve_one_category_to_show() {
        $category = factory(Category::class)->create();

        $this
            ->get(route('categories.show', ['category' => $category->id]), $this->headers)
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    /** @test  */
    public function validation_to_add_new_category() {
        $this->post('/api/categories', [], $this->headers)
            ->assertStatus(422)
            ->assertJsonFragment(
                [Lang::get('validation.required', ['attribute' => 'name'])]
            )
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonValidationErrors(
                ["name"]
            );

        $this->json('POST', route('categories.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'aasdf'
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_active'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ])
            ->assertJsonFragment([
                Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);

        $this->assertCount(0, Category::all());
    }

    /** @test  */
    public function can_add_new_category() {
        $data = [
            'name' => 'Category 1',
            'description' => 'Category description'
        ];

        $response = $this->post('/api/categories', $data, $this->headers)
            ->assertStatus(201)
            ->assertJsonFragment(
                $data
            );

        $this->assertTrue($response->json(['is_active']));
        $this->assertCount(1, Category::all());

        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test Category',
            'is_active' => false,
        ])->assertStatus(201)
            ->assertJsonFragment(
                [
                    'name' => 'test Category',
                    'is_active' => false,
                ]
            );

        $this->assertFalse($response->json('is_active'));
        $this->assertNull($response->json('description'));
    }

    /** @test  */
    public function can_edit_a_category() {
        $category = factory(Category::class)->create([
            'is_active' => false
        ]);

        $response = $this->put('/api/categories/'. $category->id, [
            'name' => 'Updating category',
            'is_active' => true,
            'description' => ''
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => 'Updating category',
                    'id' => $category->id,
                ]
            );

        $this->assertNull($response->json('description'));
        $this->assertTrue($response->json('is_active'));
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
