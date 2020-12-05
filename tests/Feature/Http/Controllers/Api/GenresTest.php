<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GenresTest extends TestCase {

    use DatabaseMigrations;

    /** @test  */
    public function list_all_genres() {

        $genres = factory(Genre::class, 100)->create();

        $this
            ->json('GET', '/api/genres')
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => $genres->first()->id
                ]
            ])
            ->assertJsonCount(100);
    }

    /** @test */
    public function can_retrieve_one_genre_to_show() {
        $genre = factory(Genre::class)->create();

        $this->json('GET', route('genres.show', ['genre' => $genre->id]))
            ->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    /** @test */
    public function validation_to_add_new_genre() {
        $this->json('POST', route('genres.store'), [])
            ->assertStatus(422)
            ->assertJsonFragment(
                [\Lang::get('validation.required', ['attribute' => 'name'])]
            )
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonValidationErrors(
                ['name']
            );

        $this->json('POST', route('genres.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'asdf'
        ])
            ->assertStatus(422)
            ->assertJsonFragment(
                [\Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])]
            )
            ->assertJsonFragment(
                [\Lang::get('validation.boolean', ['attribute' => 'is active'])]
            )
            ->assertJsonValidationErrors(
                ['name', 'is_active']
            );

        $this->assertCount(0, Genre::all());
    }

    /** @test  */
    public function can_add_new_genre() {
        $data = [
            'name' => 'Genre 1',
        ];

        $response = $this->post('/api/genres', $data, $this->headers)
            ->assertStatus(201)
            ->assertJsonFragment(
                $data
            );

        $this->assertTrue($response->json(['is_active']));
        $this->assertCount(1, Genre::all());

        $response = $this->json('POST', route('genres.store'), [
                'name' => 'Genre 1',
                'is_active' => false,
            ])
            ->assertStatus(201)
            ->assertJsonFragment(
                [
                    'name' => 'Genre 1',
                    'is_active' => false,
                ]
            );

        $this->assertFalse($response->json(['is_active']));

    }

    /** @test  */
    public function can_edit_a_genre() {
        $genre = factory(Genre::class)->create([
            'is_active' => false,
        ]);

        $response = $this->put('/api/genres/'. $genre->id, [
            'name' => 'Updating genre',
            'is_active' => true,
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => 'Updating genre',
                    'id' => $genre->id
                ]
            );

        $this->assertTrue($response->json('is_active'));
        $this->assertCount(1, Genre::all());
    }

    /** @test  */
    public function can_delete_a_genre() {
        $genre = factory(Genre::class)->create();

        $this->delete('/api/genres/'. $genre->id, $this->headers)
            ->assertStatus(204)
            ->assertNoContent();

        $this->assertCount(0, Genre::all());
    }
}
