<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController {

    private $rules;

    public function __construct() {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:'.implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id',
            'genres_id' => 'required|array|exists:genres,id',
        ];
    }

    public function store(Request $request) {
        $validated = $this->validate($request, $this->rulesStore());
        $self = $this;
        return \DB::transaction(function () use($validated, $request, $self) {
            /** @var Video $video */
            $video = Video::create($validated);
            $self->relations($video, $request);
            return $video->refresh();
        });
    }

    public function update($id, Request $request) {
        $validated = $this->validate($request, $this->rulesUpdate());
        $self = $this;

        return \DB::transaction(function () use($id, $self, $validated, $request) {
            /** @var Video $video */
            $video = $this->findOrFail($id);

            $video->update($validated);
            $self->relations($video, $request);

            return $video->fresh();
        });
    }

    protected function relations(Video $video, Request $request) {
        $video->categories()->sync($request->get('categories_id'));
        $video->genres()->sync($request->get('genres_id'));
    }

    protected function model() {
        return \App\Models\Video::class;
    }

    protected function rulesStore() {
        return $this->rules;
    }

    protected function rulesUpdate() {
        return $this->rules;
    }
}
