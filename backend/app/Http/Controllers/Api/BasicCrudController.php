<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BasicCrudController extends Controller {

    protected $paginationSize = 15;
    protected abstract function model();
    protected abstract function rulesStore();
    protected abstract function rulesUpdate();
    protected abstract function resource();
    protected abstract function resourceCollection();

    public function index() {
        $data = !$this->paginationSize ? $this->model()::all() : $this->model()::paginate($this->paginationSize);
        $resourceCollection = $this->resourceCollection();
        $refClass = new \ReflectionClass($resourceCollection);
        return $refClass->isSubclassOf(ResourceCollection::class) ?
            new $resourceCollection($data) :
            $this->resourceCollection()::collection($data);
    }

    public function store(Request $request) {
        $validated = $this->validate($request, $this->rulesStore());
        $object = $this->model()::create($validated)->refresh();
        $resource = $this->resource();
        return new $resource($object);
    }

    protected function findOrFail($id) {
        return $this->model()::findOrFail($id);
    }

    public function show($id) {
        $object = $this->findOrFail($id);
        $resource = $this->resource();
        return new $resource($object);
    }

    public function update($id, Request $request) {
        $validated = $this->validate($request, $this->rulesUpdate());
        $model = $this->findOrFail($id);
        $model->update($validated);
        $resource = $this->resource();
        return new $resource($model->fresh());
    }

    public function destroy($id) {
        $this->findOrFail($id)->delete();
        return response()->noContent();
    }
}
