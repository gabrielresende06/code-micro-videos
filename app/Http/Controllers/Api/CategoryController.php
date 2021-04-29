<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller {

    public function index() {
        return CategoryResource::collection(Category::paginate(15));
    }

    public function store(CategoryRequest $request) {
        return new CategoryResource(Category::create($request->validated())->refresh());
    }

    public function show(Category $category) {
        return new CategoryResource($category);
    }

    public function update(CategoryRequest $request, Category $category) {
        $category->update($request->all());
        return new CategoryResource($category->fresh());
    }

    public function destroy(Category $category) {
        $category->delete();
        return response()->noContent();
    }
}
