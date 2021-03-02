<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class GenresHasCategoriesRule implements Rule {

    /**
     * @var array
     */
    private $categoriesId;

    /**
     * @var Collection
     */
    private $genresId;

    /**
     * Create a new rule instance.
     *
     * @param array $categoriesId
     */
    public function __construct(array $categoriesId) {
        $this->categoriesId = collect($categoriesId)->unique();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value) {
        $this->genresId = collect($value)->unique();
        if ($this->genresId->isEmpty() || $this->categoriesId->isEmpty()) {
            return false;
        }

        $categoriesFound = collect();
        foreach ($this->genresId as $genreId) {
            $rows = $this->getRows($genreId);
            if ($rows->isEmpty()) {
                return false;
            }

            $categoriesFound = $categoriesFound->concat($rows->pluck('category_id'))->unique();
        }

        if ($categoriesFound->count() !== $this->categoriesId->count()) {
            return false;
        }

        return true;
    }

    /**
     * @param $genreId
     * @return Collection
     */
    protected function getRows($genreId): Collection {
        return \DB::table('category_genre')
                    ->where('genre_id', $genreId)
                    ->whereIn('category_id', $this->categoriesId)
                    ->get();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        return 'A genre ID must be related at least a category ID';
    }
}
