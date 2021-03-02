<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model {

    use SoftDeletes, UsesUuid, UploadFiles;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file',
        'thumb_file',
    ];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer',
    ];

    public static $fileFields = ['video_file', 'thumb_file'];

    public static function create(array $attributes = []) {
        try {
            $files = self::extractFiles($attributes);
            \DB::beginTransaction();
            /** @var Video $obj */
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);
            $obj->uploadFiles($files);
            \DB::commit();
            return $obj;
        } catch (\Exception $error) {
            if (!empty($obj)) {
                $obj->deleteFiles($files);
            }
            \DB::rollBack();
            throw $error;
        }
    }

    public function update(array $attributes = [], array $options = []) {

        $files = static::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($saved) {
                $this->uploadFiles($files);
            }
            \DB::commit();
            if ($saved && count($files)) {
                $this->deleteOldFiles();
            }
            return $saved;
        } catch (\Exception $error) {
            $this->deleteFiles($files);
            \DB::rollBack();
            throw $error;
        }
    }

    public static function handleRelations(Video $video, array $attributes) {

        if (!empty($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }

        if (!empty($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }

    public function categories() {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres() {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    public function uploadDir() {
        return $this->id;
    }
}
