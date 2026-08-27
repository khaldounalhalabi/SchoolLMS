<?php

namespace Tests\Feature;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class GlobalErrorHandlingTest extends TestCase
{
    public function test_api_requests_return_a_consistent_not_found_response(): void
    {
        $this->getJson('/api/does-not-exist')
            ->assertNotFound()
            ->assertExactJson([
                'success' => false,
                'message' => 'Resource not found.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ]);
    }

    public function test_api_requests_return_a_consistent_unauthenticated_response(): void
    {
        $this->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertExactJson([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ]);
    }

    public function test_api_validation_errors_use_the_custom_error_envelope(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta', null)
            ->assertJsonStructure([
                'message',
                'errors' => ['email', 'password'],
            ]);
    }

    public function test_http_response_exceptions_use_the_custom_error_envelope(): void
    {
        Route::middleware('api')->get('/api/testing/http-response-error', function () {
            throw new HttpResponseException(
                response()->json(['message' => 'The request cannot be processed.'], 409)
            );
        });

        $this->getJson('/api/testing/http-response-error')
            ->assertStatus(409)
            ->assertExactJson([
                'success' => false,
                'message' => 'The request cannot be processed.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ]);
    }

    public function test_unexpected_api_errors_do_not_expose_internal_details(): void
    {
        Route::get('/api/testing/global-error', function () {
            throw new \RuntimeException('Database credentials should not be exposed.');
        });

        $this->getJson('/api/testing/global-error')
            ->assertStatus(500)
            ->assertExactJson([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ]);
    }

    public function test_web_not_found_requests_use_the_custom_error_page(): void
    {
        $this->get('/definitely-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('404');
    }

    public function test_web_forbidden_requests_use_the_custom_error_page(): void
    {
        Route::get('/testing/forbidden', fn () => abort(403));

        $this->get('/testing/forbidden')
            ->assertForbidden()
            ->assertSee('Access denied')
            ->assertSee('403');
    }

    public function test_web_server_errors_use_the_custom_error_page_in_production_mode(): void
    {
        config(['app.debug' => false]);

        Route::get('/testing/server-error', function () {
            throw new \RuntimeException('Internal implementation detail.');
        });

        $this->get('/testing/server-error')
            ->assertServerError()
            ->assertSee('Something went wrong')
            ->assertSee('500')
            ->assertDontSee('Internal implementation detail.');
    }
}
