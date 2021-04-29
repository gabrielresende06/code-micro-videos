<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController {

    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
    ];

    public function store(Request $request) {
        $validated = $this->validate($request, $this->rulesStore());
        $self = $this;
        $genre = \DB::transaction(function () use ($request, $validated, $self) {
            $genre = $this->model()::create($validated);
            $self->handleRelations($genre, $request);
            return $genre;
        });
        $resource = $this->resource();
        return new $resource($genre->refresh());
    }

    public function update($id, Request $request) {
        $validated = $this->validate($request, $this->rulesUpdate());
        $self = $this;

        return \DB::transaction(function () use($id, $self, $validated, $request) {
            /** @var Genre $genre */
            $genre = $this->findOrFail($id);

            $genre->update($validated);
            $self->handleRelations($genre, $request);

            $resource = $this->resource();
            return new $resource($genre->fresh());
        });
    }

    protected function handleRelations(Genre $genre, Request $request) {
        $genre->categories()->sync($request->get('categories_id'));
    }

    protected function model() {
        return Genre::class;
    }

    protected function rulesStore() {
        return $this->rules;
    }

    protected function rulesUpdate() {
        return $this->rules;
    }

    protected function resource() {
        return GenreResource::class;
    }

    protected function resourceCollection() {
        return $this->resource();
    }
}
