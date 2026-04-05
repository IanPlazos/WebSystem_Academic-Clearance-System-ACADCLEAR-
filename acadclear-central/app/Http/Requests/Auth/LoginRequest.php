<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $this->validateCaptchaChallenge();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /**
     * Validate either Google reCAPTCHA token or local fallback checkbox.
     *
     * @throws ValidationException
     */
    protected function validateCaptchaChallenge(): void
    {
        $siteKey = (string) config('services.recaptcha.site_key');
        $secretKey = (string) config('services.recaptcha.secret_key');

        if ($siteKey !== '' && $secretKey !== '') {
            $recaptchaToken = (string) $this->input('g-recaptcha-response');

            if ($recaptchaToken === '') {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => 'Please complete the reCAPTCHA challenge.',
                ]);
            }

            try {
                $verifyResponse = Http::asForm()
                    ->timeout(10)
                    ->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => $secretKey,
                        'response' => $recaptchaToken,
                        'remoteip' => $this->ip(),
                    ]);

                if (! $verifyResponse->ok() || ! data_get($verifyResponse->json(), 'success', false)) {
                    throw ValidationException::withMessages([
                        'g-recaptcha-response' => 'Captcha verification failed. Please try again.',
                    ]);
                }
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => 'Captcha service is temporarily unavailable. Please try again.',
                ]);
            }

            return;
        }

        if (! $this->boolean('not_robot')) {
            throw ValidationException::withMessages([
                'not_robot' => 'Please confirm you are not a robot.',
            ]);
        }
    }
}
