<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoriesTest extends TestCase {

    use DatabaseMigrations, TestValidations, TestSaves;

    private $category;

    protected function setUp(): void {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    /** @test  */
    public function list_all_categories() {

        $this
            ->json('GET', '/api/categories')
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()])
            ->assertJsonCount(1);
    }

    /** @test  */
    public function can_retrieve_one_category_to_show() {
        $this
            ->json('GET', route('categories.show', ['category' => $this->category->id]))
            ->assertStatus(200)
            ->assertJson($this->category->toArray());
    }

    /**
     * @test
     * @dataProvider validationFieldsProvider
     * @param $dataProvider
     */
    public function validation_to_add_new_category($dataProvider) {
        $this->assertInvalidationInStoreAction($dataProvider['data'], $dataProvider['rule'], $dataProvider['ruleParams']);
    }

    /**
     * @test
     * @dataProvider validationFieldsProvider
     * @param $dataProvider
     */
    public function validation_to_update_category($dataProvider) {
        $this->assertInvalidationInUpdateAction($dataProvider['data'], $dataProvider['rule'], $dataProvider['ruleParams']);
    }

    /** @test
     * @param $dataProvider
     * @throws \Exception
     * @dataProvider valuesStoreProvider
     */
    public function can_add_new_category($dataProvider) {
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
    public function can_edit_a_category($dataProvider) {
        $this->category = factory(Category::class)->create(['is_active' => false, 'description' => 'description']);
        $response = $this->assertUpdate($dataProvider['data'], $dataProvider['testData'], $dataProvider['jsonData']);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
    }

    /** @test  */
    public function can_delete_a_category() {
        $this->json('DELETE', route('categories.destroy', ['category' => $this->category->id]))
            ->assertStatus(204)
            ->assertNoContent();

        $this->assertCount(0, Category::all());
    }

    protected function routeStore() {
        return route('categories.store');
    }

    protected function routeUpdate() {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model() {
        return Category::class;
    }

    public function validationFieldsProvider() {
        return [
            [['data' => ['name' => ''], 'rule' => 'required', 'ruleParams' => []]],
            [['data' => ['name' => str_repeat('a', 256)], 'rule' => 'max.string', 'ruleParams' => ['max' => 255]]],
            [['data' => ['is_active' => 'aasdf'], 'rule' => 'boolean', 'ruleParams' => []]],
        ];
    }

    public function valuesStoreProvider() {
        return [
            [
                [
                    'data' => ['name' => 'Test'],
                    'testData' => ['name' => 'Test', 'is_active' => true, 'deleted_at' => null],
                    'jsonData' => []
                ]
            ],
            [
                [
                    'data' => ['name' => 'Test', 'description' => 'description', 'is_active' => false],
                    'testData' => ['name' => 'Test', 'description' => 'description', 'is_active' => false],
                    'jsonData' => []
                ]
            ],
        ];
    }

    public function valuesUpdateProvider() {
        $data = [
            'name' => 'Test',
            'is_active' => true,
            'description' => null
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
                    'data' => array_merge($data, ['description' => 'Test']),
                    'testData' => array_merge($data, ['description' => 'Test']),
                    'jsonData' => []
                ]
            ],
            [
                [
                    'data' => array_merge($data, ['description' => '']),
                    'testData' => array_merge($data, ['description' => null]),
                    'jsonData' => []
                ]
            ],
        ];
    }
}
