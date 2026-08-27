<?php

namespace Tests\Unit;

use App\Http\Responses\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_responses_use_the_uniform_api_contract(): void
    {
        $response = ApiResponse::success(
            data: ['id' => 1],
            message: 'Loaded successfully.',
            meta: ['page' => 1],
        );

        $this->assertSame([
            'success' => true,
            'message' => 'Loaded successfully.',
            'data' => ['id' => 1],
            'errors' => null,
            'meta' => ['page' => 1],
        ], $response->getData(true));
    }

    public function test_error_responses_use_the_uniform_api_contract(): void
    {
        $response = ApiResponse::error(
            message: 'Invalid request.',
            status: 422,
            errors: ['email' => ['The email field is required.']],
        );

        $this->assertSame([
            'success' => false,
            'message' => 'Invalid request.',
            'data' => null,
            'errors' => ['email' => ['The email field is required.']],
            'meta' => null,
        ], $response->getData(true));
    }
}
