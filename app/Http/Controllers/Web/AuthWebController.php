<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordWebRequest;
use App\Models\User;
use App\Services\PasswordResetOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthWebController extends Controller
{
    public function __construct(private readonly PasswordResetOtpService $otpService) {}

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! $user->is_active) {
            return back()->withErrors(['email' => 'Invalid credentials or account deactivated.'])->withInput();
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->withInput();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPassword(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $this->otpService->send($user->email);
        }

        return redirect()
            ->route('password.reset', ['email' => $request->email])
            ->with('status', __('If an account exists for this email, a reset code has been sent.'));
    }

    public function showResetPassword(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(ResetPasswordWebRequest $request): RedirectResponse
    {
        if (! $this->otpService->verify($request->email, $request->otp)) {
            return back()->withErrors(['otp' => __('This code is invalid or has expired.')])->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['otp' => __('This code is invalid or has expired.')])->withInput();
        }

        $user->forceFill(['password' => $request->password])->setRememberToken(Str::random(60));
        $user->save();
        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', __('Your password has been reset.'));
    }
}
