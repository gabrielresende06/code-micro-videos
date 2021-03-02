<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class GenreTest extends TestCase {

    use DatabaseMigrations;

    public function testList() {
        factory(Genre::class)->create();

        $genres = Genre::all();
        $this->assertCount(1, $genres);
        $categoryKeys = array_keys( $genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $categoryKeys);
    }

    public function testCreate() {
        $genre = Genre::create([
            'name' => 'test1',
        ]);
        $genre->refresh();
        $this->assertTrue(Uuid::isValid($genre->id));
        $this->assertEquals('test1', $genre->name);
        $this->assertTrue($genre->is_active);

        $genre = Genre::create([
            'name' => 'test1',
            'is_active' => false
        ]);
        $genre->refresh();
        $this->assertFalse($genre->is_active);

        $genre = Genre::create([
            'name' => 'test1',
            'is_active' => true
        ]);
        $genre->refresh();
        $this->assertTrue( $genre->is_active);
    }

    public function testUpdate() {
        $genre = factory(Genre::class)->create([
            'is_active' => false
        ]);

        $genre->update([
            'name' => 'test_name_updated',
            'is_active' => true
        ]);

        $this->assertTrue(Uuid::isValid($genre->id));
        $this->assertEquals('test_name_updated', $genre->name);
        $this->assertTrue($genre->is_active);
    }

    public function testDelete() {
        $genre = factory(Genre::class)->create();

        $genres = Genre::all();
        $this->assertCount(1, $genres);
        $genre->delete();

        $genresAfterDelete = Genre::all();
        $this->assertCount(0, $genresAfterDelete);
    }
}
