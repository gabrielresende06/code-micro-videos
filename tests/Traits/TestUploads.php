<?php

namespace Tests\Traits;

use Illuminate\Http\UploadedFile;

trait TestUploads {

    /**
     * @param string $field
     * @param string $extension
     * @param int $maxSize
     * @param string $rule
     * @param array $rulesParams
     */
    protected function assertInvalidationFile(
        string $field,
        string $extension,
        int $maxSize,
        string $rule,
        array $rulesParams = []
    ) {
        $routes = [
            [
                'method' => 'POST',
                'route' => $this->routeStore()
            ],
            [
                'method' => 'PUT',
                'route' => $this->routeUpdate()
            ]
        ];

        foreach ($routes as $route) {
            $file = UploadedFile::fake()->create("$field.1$extension");
            $response = $this->json($route['method'], $route['route'], [$field => $file]);

            $this->assertInvalidationFields($response, [$field], $rule, $rulesParams);

            $file = UploadedFile::fake()->create("$field.$extension")->size($maxSize + 1);
            $response = $this->json($route['method'], $route['route'], [$field => $file]);

            $this->assertInvalidationFields($response, [$field], 'max.file', ['max' => $maxSize]);
        }
    }
}
