<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        ?string $message = null,
        ?array $meta = null,
        int $status = 200,
        array $headers = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'meta' => $meta,
        ], $status, $headers);
    }

    public static function error(
        string $message,
        int $status,
        array $errors = [],
        ?array $meta = null,
        array $headers = [],
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors ?: null,
            'meta' => $meta,
        ], $status, $headers);
    }

    public static function normalizeError(JsonResponse $response): JsonResponse
    {
        if ($response->isSuccessful() || $response->getStatusCode() < 400) {
            return $response;
        }

        $payload = $response->getData(true);

        if (is_array($payload) && ($payload['success'] ?? null) === false) {
            return $response;
        }

        return self::error(
            is_array($payload) && isset($payload['message']) ? $payload['message'] : 'Request failed.',
            $response->getStatusCode(),
            is_array($payload) && isset($payload['errors']) && is_array($payload['errors']) ? $payload['errors'] : [],
            null,
            $response->headers->all(),
        );
    }
}
