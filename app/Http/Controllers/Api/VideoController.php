<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
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
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id' => [
                'required',
                'array',
                'exists:genres,id,deleted_at,NULL'
            ],
            'video_file' => 'mimetypes:video/mp4|max:'.Video::VIDEO_FILE_MAX_SIZE,
            'trailer_file' => 'mimetypes:video/mp4|max:'.Video::TRAILER_FILE_MAX_SIZE,
            'banner_file' => 'image|max:'.Video::BANNER_FILE_MAX_SIZE,
            'thumb_file' => 'image|max:'.Video::THUMB_FILE_MAX_SIZE,
        ];
    }

    public function store(Request $request) {
    // random changes
        $this->addRuleIfGenreHasCategories($request);
        $validated = $this->validate($request, $this->rulesStore());

        /** @var Video $video */
        $video = $this->model()::create($validated);
        $resource = $this->resource();
        return new $resource($video->refresh());
    }

    public function update($id, Request $request) {
        $this->addRuleIfGenreHasCategories($request);
        $validated = $this->validate($request, $this->rulesUpdate());

        /** @var Video $video */
        $video = $this->findOrFail($id);
        $video->update($validated);
        $resource = $this->resource();
        return new $resource($video->fresh());
    }

    protected function addRuleIfGenreHasCategories(Request $request) {
        $categoriesId = $request->get('categories_id');
        $categoriesId = is_array($categoriesId) ? $categoriesId : [];
        $this->rules['genres_id'][] = new GenresHasCategoriesRule($categoriesId);
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

    protected function resource() {
        return VideoResource::class;
    }

    protected function resourceCollection() {
        return $this->resource();
    }
}
