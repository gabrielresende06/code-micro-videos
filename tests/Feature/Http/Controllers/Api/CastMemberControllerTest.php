<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase {

    use DatabaseMigrations, TestValidations, TestSaves;

    private $castMember;

    protected function setUp(): void {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
    }

    /** @test  */
    public function index() {

        $this
            ->json('GET', route('cast_members.index'))
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()])
            ->assertJsonCount(1);
    }

    /** @test  */
    public function show() {
        $this
            ->json('GET', route('cast_members.show', ['cast_member' => $this->castMember->id]))
            ->assertStatus(200)
            ->assertJson($this->castMember->toArray());
    }

    /**
     * @test
     * @dataProvider validationFieldsProvider
     * @param $dataProvider
     */
    public function validation_to_add_new_cast_member($dataProvider) {
        $this->assertInvalidationInStoreAction($dataProvider['data'], $dataProvider['rule'], $dataProvider['ruleParams']);
    }

    /**
     * @test
     * @dataProvider validationFieldsProvider
     * @param $dataProvider
     */
    public function validation_to_update_cast_member($dataProvider) {
        $this->assertInvalidationInUpdateAction($dataProvider['data'], $dataProvider['rule'], $dataProvider['ruleParams']);
    }

    /** @test
     * @param $dataProvider
     * @throws \Exception
     * @dataProvider valuesStoreProvider
     */
    public function store($dataProvider) {
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
    public function update($dataProvider) {
        $response = $this->assertUpdate($dataProvider['data'], $dataProvider['testData'], $dataProvider['jsonData']);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
    }

    /** @test  */
    public function can_delete_a_category() {
        $response = $this->json('DELETE', route('cast_members.destroy', ['cast_member' => $this->castMember->id]));
        $response->assertStatus(204)
            ->assertNoContent();

        $this->assertCount(0, CastMember::all());
    }

    protected function routeStore() {
        return route('cast_members.store');
    }

    protected function routeUpdate() {
        return route('cast_members.update', ['cast_member' => $this->castMember->id]);
    }

    protected function model() {
        return CastMember::class;
    }

    public function validationFieldsProvider() {
        return [
            [['data' => ['name' => ''], 'rule' => 'required', 'ruleParams' => []]],
            [['data' => ['name' => str_repeat('a', 256)], 'rule' => 'max.string', 'ruleParams' => ['max' => 255]]],
            [['data' => ['type' => ''], 'rule' => 'required', 'ruleParams' => []]],
            [['data' => ['type' => 's'], 'rule' => 'in', 'ruleParams' => []]],
        ];
    }

    public function valuesStoreProvider() {
        return [
            [
                [
                    'data' => ['name' => 'Test' , 'type' => CastMember::TYPE_DIRECTOR],
                    'testData' => ['name' => 'Test', 'type' => CastMember::TYPE_DIRECTOR, 'deleted_at' => null],
                    'jsonData' => []
                ]
            ],
            [
                [
                    'data' => ['name' => 'Test' , 'type' => CastMember::TYPE_ACTOR],
                    'testData' => ['name' => 'Test', 'type' => CastMember::TYPE_ACTOR, 'deleted_at' => null],
                    'jsonData' => []
                ]
            ],
        ];
    }

    public function valuesUpdateProvider() {
        $data = [
            'name' => 'Test update',
            'type' => CastMember::TYPE_ACTOR,
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
