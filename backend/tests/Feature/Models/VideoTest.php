<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class VideoTest extends TestCase {

    use DatabaseMigrations;

    protected $data;

    protected function setUp(): void {
        parent::setUp();
        $this->data = [
            'title' => 'title' ,
            'description' => 'description',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,

        ];
    }

    public function testList() {
        factory(Video::class)->create();
        $videos = Video::all();
        $this->assertCount(1, $videos);
        $videosKeys = array_keys($videos->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
              'id',
              'title',
              'description',
              'year_launched',
              'opened',
              'rating',
              'duration',
              'created_at',
              'updated_at',
              'deleted_at',
              'video_file',
              'thumb_file',
              'banner_file',
              'trailer_file',
            ],
            $videosKeys
        );
    }

    public function testCreateWithBasiFields() {
        $video =  Video::create($this->data);
        $video->refresh();

        $this->assertTrue(Uuid::isValid($video->id));
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = Video::create($this->data + [ 'opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data +['opened' => true]);
    }

    public function testCreateWithRelations() {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = Video::create($this->data + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id]
            ]);
        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testRollbackCreate() {

        $hasThrewException = false;
        try {
            Video::create($this->data + ['categories_id' => [0, 1, 2]]);
        } catch (QueryException $ex) {
            $this->assertCount(0, Video::all());
            $hasThrewException = true;
        }

        $this->assertTrue($hasThrewException);
    }

    public function testeUpdateWithBasicFields() {
        $video = factory(Video::class)->create(['opened' => false]);
        $video->update($this->data);
        $video->refresh();
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = factory(Video::class)->create(['opened' => false]);
        $video->update($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);

    }

    public function testUpdateWithRelations() {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = factory(Video::class)->create();
        $video->update($this->data + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
            ]);

        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testRollbackUpdate() {

        $video = factory(Video::class)->create();
        $title = $video->title;
        $data = [
                'title' => 'title' ,
                'description' => 'description',
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0, 1, 2]
            ];

        $hasThrewException = false;
        try {
            $video->update($data);
        } catch (QueryException $ex) {
            $this->assertCount(1, Video::all());
            $this->assertDatabaseHas('videos', [
                'title' => $title,
                'id' => $video->id
            ]);
            $hasThrewException = true;
        }

        $this->assertTrue($hasThrewException);
    }

    public function testHandleRelations() {
        $video = factory(Video::class)->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        $category = factory(Category::class)->create();
        Video::handleRelations($video, ['categories_id' => [$category->id]]);

        $video->refresh();
        $this->assertCount(1, $video->categories);

        $genre = factory(Genre::class)->create();
        Video::handleRelations($video, ['genres_id' => [$genre->id]]);

        $video->refresh();
        $this->assertCount(1, $video->genres);

        $video->categories()->delete();
        $video->genres()->delete();

        Video::handleRelations($video, [
            ['categories_id' => [$category->id]],
            ['genres_id' => [$genre->id]]
        ]);

        $video->refresh();
        $this->assertCount(1, $video->genres);
        $this->assertCount(1, $video->categories);
    }

    public function testSyncGenres() {
        $genresId = factory(Genre::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();

        Video::handleRelations($video, ['genres_id' => [$genresId[0]]]);

        $this->assertHasGenre($video->id, $genresId[0]);

        Video::handleRelations($video, ['genres_id' => [$genresId[1], $genresId[2]]]);
        $this->assertDatabaseMissing('genre_video', ['genre_id' => $genresId[0], 'video_id' => $video->id]);
        $this->assertHasGenre($video->id, $genresId[1]);
        $this->assertHasGenre($video->id, $genresId[2]);
    }

    public function testSyncCategories() {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();

        Video::handleRelations($video, ['categories_id' => [$categoriesId[0]]]);

        $this->assertHasCategory($video->id, $categoriesId[0]);

        Video::handleRelations($video, ['categories_id' => [$categoriesId[1], $categoriesId[2]]]);
        $this->assertDatabaseMissing('category_video', ['category_id' => $categoriesId[0], 'video_id' => $video->id]);
        $this->assertHasCategory($video->id, $categoriesId[1]);
        $this->assertHasCategory($video->id, $categoriesId[2]);
    }

    protected function assertHasCategory($videoId, $categoryId) {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    protected function assertHasGenre($videoId, $genreId) {
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $videoId,
            'genre_id' => $genreId
        ]);
    }
}
