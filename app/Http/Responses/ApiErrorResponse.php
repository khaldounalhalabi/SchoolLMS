<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiErrorResponse
{
    public static function make(string $message, int $status, array $errors = [], array $headers = []): JsonResponse
    {
        return ApiResponse::error($message, $status, $errors, null, $headers);
    }

    public static function normalize(JsonResponse $response): JsonResponse
    {
        return ApiResponse::normalizeError($response);
    }
}
