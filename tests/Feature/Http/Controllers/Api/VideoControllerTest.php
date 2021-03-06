<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase {

    use DatabaseMigrations, TestValidations, TestSaves, TestUploads, TestResources;

    private $video;
    private $categories;
    private $genres;
    private $serializedFields = [
        'id',
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'video_file_url',
        'trailer_file_url',
        'banner_file_url',
        'thumb_file_url',
        'duration',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function setUp(): void {
        parent::setUp();
        $this->video = factory(Video::class)->create([
            'opened' => false
        ]);
    }

    /** @test  */
    public function index() {

        $response = $this
            ->json('GET', route('videos.index'))
            ->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'meta' => [],
                'links' => [],
            ]);

        $this->assertResource($response, VideoResource::collection(collect([$this->video])));
    }

    /** @test  */
    public function show() {
        $response = $this
            ->json('GET', route('videos.show', ['video' => $this->video->id]))
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);

        $this->assertResource($response, new VideoResource($this->video));
    }

    /**
     * @test
     * @dataProvider validationFieldsProvider
     * @param $dataProvider
     */
    public function validation_to_add_new_video($dataProvider) {
        $this->assertInvalidationInStoreAction($dataProvider['data'], $dataProvider['rule'], $dataProvider['ruleParams']);
    }

    /**
     * @test
     * @dataProvider validationFieldsProvider
     * @param $dataProvider
     */
    public function validation_to_update_video($dataProvider) {
        $this->assertInvalidationInUpdateAction($dataProvider['data'], $dataProvider['rule'], $dataProvider['ruleParams']);
    }

    public function testInvalidationVideoField() {
        $this->assertInvalidationFile('video_file', 'mp4', Video::VIDEO_FILE_MAX_SIZE, 'mimetypes', ['values' => 'video/mp4']);
    }

    /** @test
     * @param $dataProvider
     * @throws \Exception
     * @dataProvider valuesStoreProvider
     */
    public function store($dataProvider) {
        $categories = factory(Category::class, 2)->create();
        $genres = factory(Genre::class, 2)->create();
        $genres->each(function ($genre) use ($categories) {
           $genre->categories()->sync($categories->pluck('id')->toArray());
        });
        $response = $this->assertStore(
            $dataProvider['data'] + [
                'genres_id' => $genres->pluck('id')->toArray(),
                'categories_id' => $categories->pluck('id')->toArray()
            ],
            $dataProvider['testData'], $dataProvider['jsonData']
        );
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertDatabaseHas('category_video', [
            'video_id' => $response->json('data.id'),
            'category_id' => $categories->first()->id,
        ]);
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $response->json('data.id'),
            'genre_id' => $genres->first()->id,
        ]);
        $this->assertResource($response, new VideoResource(Video::find($response->json('data.id'))));
    }

    /**
     * @test
     * @param $dataProvider
     * @throws \Exception
     * @dataProvider valuesUpdateProvider
     */
    public function update($dataProvider) {
        $categories = factory(Category::class, 3)->create();
        $genres = factory(Genre::class, 3)->create();
        $genres->each(function ($genre) use ($categories) {
            $genre->categories()->sync($categories->pluck('id')->toArray());
        });
        $response = $this->assertUpdate(
            $dataProvider['data'] + ['genres_id' => $genres->pluck('id')->toArray(), 'categories_id' => $categories->pluck('id')->toArray()],
            $dataProvider['testData'], $dataProvider['jsonData']
        );
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertResource($response, new VideoResource(Video::find($response->json('data.id'))));
    }

    public function testStoreWithFiles() {
        \Storage::fake();
        $files = $this->getfiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $data = [
            'title' => 'title' ,
            'description' => 'description',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
            'categories_id' => [ $category->id ],
            'genres_id' => [ $genre->id ],
        ];

        $response = $this->json('POST', $this->routeStore(), $data + $files);
        $response->assertStatus(201);
        $id = $response->json('data.id');
        foreach ($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }

    }

    public function testUpdateWithFiles() {
        \Storage::fake();
        $files = $this->getfiles();

        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $data = [
            'title' => 'title' ,
            'description' => 'description',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
            'categories_id' => [ $category->id ],
            'genres_id' => [ $genre->id ],
        ];

        $response = $this->json('PUT', $this->routeUpdate(), $data + $files);
        $response->assertStatus(200);
        $id = $response->json('data.id');
        foreach ($files as $file) {
            \Storage::assertExists("$id/{$file->hashName()}");
        }
    }

    public function getFiles() {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4')
        ];
    }

    /** @test  */
    public function can_delete_video() {
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204)
            ->assertNoContent();

        $this->assertCount(0, Video::all());
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function routeStore() {
        return route('videos.store');
    }

    protected function routeUpdate() {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model() {
        return Video::class;
    }

    public function validationFieldsProvider() {
        return [

            [['data' => ['title' => ''], 'rule' => 'required', 'ruleParams' => []]],
            [['data' => ['description' => ''], 'rule' => 'required', 'ruleParams' => []]],
            [['data' => ['year_launched' => ''], 'rule' => 'required', 'ruleParams' => []]],
            [['data' => ['rating' => ''], 'rule' => 'required', 'ruleParams' => []]],
            [['data' => ['duration' => ''], 'rule' => 'required', 'ruleParams' => []]],
            [['data' => ['categories_id' => ''], 'rule' => 'required', 'ruleParams' => []]],
            [['data' => ['genres_id' => ''], 'rule' => 'required', 'ruleParams' => []]],

            [['data' => ['duration' => 'a'], 'rule' => 'integer', 'ruleParams' => []]],

            [['data' => ['opened' => 'a'], 'rule' => 'boolean', 'ruleParams' => []]],

            [['data' => ['categories_id' => 'a'], 'rule' => 'array', 'ruleParams' => []]],
            [['data' => ['genres_id' => 'a'], 'rule' => 'array', 'ruleParams' => []]],

            [['data' => ['categories_id' => [100]], 'rule' => 'exists', 'ruleParams' => []]],
            [['data' => ['genres_id' => [100]], 'rule' => 'exists', 'ruleParams' => []]],

            [['data' => ['rating' => 0], 'rule' => 'in', 'ruleParams' => []]],

            [['data' => ['year_launched' => 'a'], 'rule' => 'date_format', 'ruleParams' => ['format' => 'Y']]],

            [['data' => ['title' => str_repeat('a', 256)], 'rule' => 'max.string', 'ruleParams' => ['max' => 255]]],

        ];
    }

    public function valuesStoreProvider() {

        $data = [
            'title' => 'title' , 'description' => 'description', 'year_launched' => 2010, 'rating' => Video::RATING_LIST[0], 'duration' => 90,
        ];
        return [
            [
                [
                    'data' => $data,
                    'testData' => $data + ['opened' => false, 'deleted_at' => null],
                    'jsonData' => []
                ]
            ],
            [
                [
                    'data' => $data + ['opened' => true],
                    'testData' => $data + ['opened' => true, 'deleted_at' => null],
                    'jsonData' => []
                ]
            ],
            [
                [
                    'data' => $data + ['rating' => Video::RATING_LIST[1]],
                    'testData' => $data + ['rating' => Video::RATING_LIST[1], 'deleted_at' => null],
                    'jsonData' => []
                ]
            ],
        ];
    }

    public function valuesUpdateProvider() {
        $data = [
            'title' => 'title' , 'description' => 'description', 'year_launched' => 2010, 'rating' => Video::RATING_LIST[0], 'duration' => 90,
        ];
        return [
            [
                [
                    'data' => $data,
                    'testData' => $data + ['deleted_at' => null],
                    'jsonData' => []
                ]
            ],
            [
                [
                    'data' => $data + ['opened' => true],
                    'testData' => $data + ['opened' => true, 'deleted_at' => null],
                    'jsonData' => []
                ]
            ],
            [
                [
                    'data' => $data + ['rating' => Video::RATING_LIST[1]],
                    'testData' => $data + ['rating' => Video::RATING_LIST[1], 'deleted_at' => null],
                    'jsonData' => []
                ]
            ],
        ];
    }
}
