<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\PasswordResetOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly PasswordResetOtpService $otpService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Your account has been deactivated.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return ApiResponse::success(data: [
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(message: 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(data: new UserResource($request->user()));
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        $user->notify(new SystemNotification(
            'Welcome to SchoolLMS',
            'Your account has been created successfully.',
            route('dashboard'),
            'account',
        ));

        return ApiResponse::success(data: ['user' => new UserResource($user)], status: 201);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $this->otpService->send($user->email);
        }

        return ApiResponse::success(message: __('If an account exists for this email, a reset code has been sent.'));
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        if (! $this->otpService->verify($request->email, $request->otp)) {
            return response()->json(['message' => __('This code is invalid or has expired.')], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => __('This code is invalid or has expired.')], 422);
        }

        $user->forceFill(['password' => $request->password])->setRememberToken(Str::random(60));
        $user->save();
        event(new PasswordReset($user));

        return ApiResponse::success(message: __('Your password has been reset.'));
    }
}
