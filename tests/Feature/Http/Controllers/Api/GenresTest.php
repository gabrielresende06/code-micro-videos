<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenresTest extends TestCase {

    use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;

    protected function setUp(): void {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
    }

    /** @test  */
    public function list_all_genres() {
        $this
            ->json('GET', route('genres.index'))
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()])
            ->assertJsonCount(1);
    }

    /** @test */
    public function can_retrieve_one_genre_to_show() {
        $this->json('GET', route('genres.show', ['genre' => $this->genre->id]))
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }

    /**
     * @test
     * @dataProvider validationFieldsProvider
     * @param $dataProvider
     */
    public function validation_to_add_new_genre($dataProvider) {
        $this->assertInvalidationInStoreAction($dataProvider['data'], $dataProvider['rule'], $dataProvider['ruleParams']);
    }

    /**
     * @test
     * @dataProvider validationFieldsProvider
     * @param $dataProvider
     */
    public function validation_to_update_genre($dataProvider) {
        $this->assertInvalidationInUpdateAction($dataProvider['data'], $dataProvider['rule'], $dataProvider['ruleParams']);
    }

    /** @test
     * @param $dataProvider
     * @throws \Exception
     * @dataProvider valuesStoreProvider
     */
    public function can_add_new_genre($dataProvider) {
        $categories = factory(Category::class, 2)->create();
        $response = $this->assertStore($dataProvider['data'] + ['categories_id' => $categories->pluck('id')->toArray()], $dataProvider['testData'], $dataProvider['jsonData']);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $response->json('id'),
            'category_id' => $categories->first()->id,
        ]);
    }

    /**
     * @test
     * @param $dataProvider
     * @throws \Exception
     * @dataProvider valuesUpdateProvider
     */
    public function can_edit_a_genre($dataProvider) {
        $categories = factory(Category::class, 3)->create();
        $this->genre = factory(Genre::class)->create(['is_active' => false]);
        $response = $this->assertUpdate($dataProvider['data'] + ['categories_id' => $categories->pluck('id')->toArray()],
            $dataProvider['testData'], $dataProvider['jsonData']);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
    }

    public function testRollbackStore() {

        $data = [ 'name' => 'title' , 'is_active' => true];
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->once()
            ->withAnyArgs()
            ->andReturn($data);

        $controller->shouldReceive('rulesStore')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $request = \Mockery::mock(Request::class);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException('test'));

        $hasThrewException = false;
        try {
            $controller->store($request);
        } catch (TestException $ex) {
            $this->assertCount(1, Genre::all());
            $hasThrewException = true;
        }

        $this->assertTrue($hasThrewException);
    }


    public function testRollbackUpdate() {

        $data = [ 'name' => 'title' , 'is_active' => true];
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->once()
            ->withAnyArgs()
            ->andReturn($data);

        $controller->shouldReceive('rulesUpdate')
            ->once()
            ->withAnyArgs()
            ->andReturn([]);

        $request = \Mockery::mock(Request::class);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException('test'));

        $hasThrewException = false;
        try {
            $controller->update($this->genre->id ,$request);
        } catch (TestException $ex) {
            $this->assertCount(1, Genre::all());
            $hasThrewException = true;
        }

        $this->assertTrue($hasThrewException);
    }

    /** @test  */
    public function can_delete_a_genre() {
        $this->json('DELETE', route('genres.destroy', ['genre' => $this->genre->id]))
            ->assertStatus(204)
            ->assertNoContent();

        $this->assertCount(0, Genre::all());
    }

    protected function routeStore() {
        return route('genres.store');
    }

    protected function routeUpdate() {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function model() {
        return Genre::class;
    }

    public function validationFieldsProvider() {
        return [
            [['data' => ['name' => null], 'rule' => 'required', 'ruleParams' => []],],
            [['data' => ['name' => str_repeat('a', 256)], 'rule' => 'max.string', 'ruleParams' => ['max' => 255]],],
            [['data' => ['is_active' => 'asdf'], 'rule' => 'boolean', 'ruleParams' => []]],

            [['data' => ['categories_id' => ''], 'rule' => 'required', 'ruleParams' => []]],

            [['data' => ['categories_id' => 'a'], 'rule' => 'array', 'ruleParams' => []]],
        ];
    }

    public function valuesStoreProvider() {
        return [
            [
                [
                    'data' => ['name' =>   'Test'],
                    'testData' => ['name' => 'Test', 'is_active' => true, 'deleted_at' => null],
                    'jsonData' => []
                ]
            ],
            [
                [
                    'data' => ['name' =>   'Test', 'is_active' => false],
                    'testData' => ['name' => 'Test', 'is_active' => false],
                    'jsonData' => []
                ]
            ],
        ];
    }

    public function valuesUpdateProvider() {
        $data = [
            'name' => 'Updating genre',
            'is_active' => true,
        ];
        return [
            [
                [
                    'data' => $data,
                    'testData' => $data + ['deleted_at' => null],
                    'jsonData' => []
                ]
            ],
        ];
    }
}
