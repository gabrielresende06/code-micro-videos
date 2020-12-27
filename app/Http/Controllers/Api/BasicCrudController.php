<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller {

    protected abstract function model();
    protected abstract function rulesStore();
    protected abstract function rulesUpdate();

    public function index() {
        return $this->model()::all();
    }

    public function store(Request $request) {
        $validated = $this->validate($request, $this->rulesStore());
        return $this->model()::create($validated)->refresh();
    }

    protected function findOrFail($id) {
        return $this->model()::findOrFail($id);

//        $model = $this->model();
//        $keyName = (new $model)->getRouteKeyName();
//        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id) {
        return $this->findOrFail($id);
    }

    public function update($id, Request $request) {
        $validated = $this->validate($request, $this->rulesUpdate());
        $model = $this->findOrFail($id);
        $model->update($validated);
        return $model->fresh();
    }

    public function destroy($id) {
        $this->findOrFail($id)->delete();
        return response()->noContent();
    }
}
