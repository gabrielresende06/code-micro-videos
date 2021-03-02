<?php

namespace Tests\Unit\Models;

use App\Models\Traits\UsesUuid;
use App\Models\Video;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class VideoTest extends TestCase {

    private $video;

    protected function setUp(): void {
        parent::setUp();
        $this->video = new Video();
    }

    public function testIfUseTraits() {
        $traits = [
            SoftDeletes::class,
            UsesUuid::class
        ];

        $videoTraits = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $videoTraits);
    }

    public function testFillableAttributes() {
        $fillable = ['title', 'description', 'year_launched', 'opened', 'rating',  'duration',];
        $this->assertEquals($fillable, $this->video->getFillable());
    }

    public function testKeyType() {
        $this->assertEquals('string', $this->video->getKeyType());
    }

    public function testIncrementing() {
        $this->assertFalse($this->video->getIncrementing());
    }

    public function testDatesAttribute() {
        $dates = collect(['deleted_at', 'created_at', 'updated_at']);
        foreach ($dates as $date) {
            $this->assertContains($date, $this->video->getDates());
        }

        $this->assertCount($dates->count(), $this->video->getDates());
    }

    public function testCasts() {
        $casts = ['id' => 'string',  'opened' => 'boolean', 'year_launched' => 'integer', 'duration' => 'integer',];
        $this->assertEquals($casts, $this->video->getCasts());
    }
}
