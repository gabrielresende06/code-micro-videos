<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
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
            ->json('GET', '/api/genres')
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
        $response = $this->assertStore($dataProvider['data'], $dataProvider['testData'], $dataProvider['jsonData']);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
    }

    /**
     * @test
     * @param $dataProvider
     * @throws \Exception
     * @dataProvider valuesUpdateProvider
     */
    public function can_edit_a_genre($dataProvider) {
        $this->genre = factory(Genre::class)->create(['is_active' => false]);
        $response = $this->assertUpdate($dataProvider['data'], $dataProvider['testData'], $dataProvider['jsonData']);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
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
