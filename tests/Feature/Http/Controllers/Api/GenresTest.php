<?php

namespace Tests\Feature;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class GenresTest extends TestCase {

    use DatabaseMigrations;

    /** @test  */
    public function list_all_genres() {

        $genres = factory(Genre::class, 100)->create();

        $this
            ->get('/api/genres', $this->headers)
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => $genres->first()->id
                ]
            ])
            ->assertJsonCount(100);
    }

    /** @test  */
    public function name_is_required_to_add_new_genre() {
        $this->post('/api/genres', [], $this->headers)
            ->assertStatus(422)
            ->assertJsonFragment(
                ["The name field is required."]
            );

        $this->assertCount(0, Genre::all());
    }

    /** @test  */
    public function can_add_new_genre() {
        $data = [
            'name' => 'Genre 1',
        ];

        $this->post('/api/genres', $data, $this->headers)
            ->assertStatus(201)
            ->assertJsonFragment(
                $data
            );

        $this->assertCount(1, Genre::all());
    }

    /** @test  */
    public function can_edit_a_genre() {
        $genre = factory(Genre::class)->create();

        $this->put('/api/genres/'. $genre->id, [
            'name' => 'Updating genre'
        ], $this->headers)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => 'Updating genre',
                    'id' => $genre->id
                ]
            );

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
