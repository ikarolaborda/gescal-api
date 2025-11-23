<?php

namespace Tests\Traits;

use Illuminate\Testing\TestResponse;

trait JsonApiTestHelpers
{
    /**
     * Visit the given URI with a POST request, expecting JSON:API format.
     */
    protected function postJsonApi(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $headers = array_merge([
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ], $headers);

        return $this->json('POST', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PATCH request, expecting JSON:API format.
     */
    protected function patchJsonApi(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $headers = array_merge([
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ], $headers);

        return $this->json('PATCH', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a DELETE request, expecting JSON:API format.
     */
    protected function deleteJsonApi(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $headers = array_merge([
            'Content-Type' => 'application/vnd.api+json',
            'Accept' => 'application/vnd.api+json',
        ], $headers);

        return $this->json('DELETE', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a GET request, expecting JSON:API format.
     */
    protected function getJsonApi(string $uri, array $headers = []): TestResponse
    {
        $headers = array_merge([
            'Accept' => 'application/vnd.api+json',
        ], $headers);

        return $this->json('GET', $uri, [], $headers);
    }
}
