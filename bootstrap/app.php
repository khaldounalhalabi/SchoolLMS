<?php

use App\Http\Middleware\ActualRoleMiddleware;
use App\Http\Middleware\ApiErrorResponseMiddleware;
use App\Http\Middleware\ImpersonationMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'actual-role' => ActualRoleMiddleware::class,
            'impersonation' => ImpersonationMiddleware::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->statefulApi();

        $middleware->api(prepend: [
            HandleCors::class,
            ApiErrorResponseMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isJsonRequest = static fn (Request $request, Throwable $exception): bool => $request->is('api/*') || $request->expectsJson();

        $apiError = static fn (string $message, int $status, array $errors = [], array $headers = []): JsonResponse => ApiResponse::error($message, $status, $errors, null, $headers);

        $exceptions->shouldRenderJsonWhen($isJsonRequest);

        $exceptions->render(function (HttpResponseException $exception, Request $request) use ($isJsonRequest) {
            $response = $exception->getResponse();

            if (! $isJsonRequest($request, $exception) || ! ($response instanceof JsonResponse)) {
                return null;
            }

            return ApiResponse::normalizeError($response);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($apiError, $isJsonRequest) {
            if (! $isJsonRequest($request, $exception)) {
                return null;
            }

            return $apiError($exception->getMessage() ?: 'Unauthenticated.', 401);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($apiError, $isJsonRequest) {
            if (! $isJsonRequest($request, $exception)) {
                return null;
            }

            return $apiError($exception->getMessage(), $exception->status, $exception->errors());
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($apiError, $isJsonRequest) {
            if (! $isJsonRequest($request, $exception)) {
                return null;
            }

            $status = $exception->getStatusCode();
            $message = $exception instanceof NotFoundHttpException
                ? 'Resource not found.'
                : ($exception->getMessage()
                    ?: (SymfonyResponse::$statusTexts[$status] ?? 'Request failed.'));

            return $apiError($message, $status, [], $exception->getHeaders());
        });

        $exceptions->render(function (Throwable $exception, Request $request) use ($apiError, $isJsonRequest) {
            if (! $isJsonRequest($request, $exception) || $exception instanceof HttpResponseException) {
                return null;
            }

            return $apiError('An unexpected error occurred.', 500);
        });

        $exceptions->respond(function ($response, Throwable $exception, Request $request) use ($apiError, $isJsonRequest) {
            if (! $isJsonRequest($request, $exception)
                || ! $response instanceof JsonResponse
                || $response->isSuccessful()
            ) {
                return $response;
            }

            $payload = $response->getData(true);

            if (is_array($payload) && ($payload['success'] ?? null) === false) {
                return $response;
            }

            return $apiError(
                is_array($payload) && isset($payload['message']) ? $payload['message'] : 'Request failed.',
                $response->getStatusCode(),
                is_array($payload) && isset($payload['errors']) && is_array($payload['errors']) ? $payload['errors'] : [],
                $response->headers->all(),
            );
        });
    })->create();
