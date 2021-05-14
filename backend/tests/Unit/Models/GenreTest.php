<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class GenreTest extends TestCase {

    private $genre;

    protected function setUp(): void {
        parent::setUp();
        $this->genre = new Genre();
    }

    public function testIfUseTraits() {
        $traits = [
            SoftDeletes::class,
            UsesUuid::class
        ];

        $genreTraits = array_keys(class_uses(Genre::class));
        $this->assertEquals($traits, $genreTraits);
    }

    public function testFillableAttributes() {
        $fillable = ['name', 'is_active'];
        $this->assertEquals($fillable, $this->genre->getFillable());
    }

    public function testKeyType() {
        $this->assertEquals('string', $this->genre->getKeyType());
    }

    public function testIncrementing() {
        $this->assertFalse($this->genre->getIncrementing());
    }

    public function testDatesAttribute() {
        $dates = collect(['deleted_at', 'created_at', 'updated_at']);
        foreach ($dates as $date) {
            $this->assertContains($date, $this->genre->getDates());
        }

        $this->assertCount($dates->count(), $this->genre->getDates());
    }

    public function testCasts() {
        $casts = ['is_active' => 'boolean', 'id' => 'string'];
        $this->assertEquals($casts, $this->genre->getCasts());
    }
}
