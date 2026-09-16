<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('admin-login', function (Request $request): Limit {
            $email = mb_strtolower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('user-verification', function (Request $request): array {
            $userId = $request->user()?->getAuthIdentifier() ?? 'guest';

            return [
                Limit::perMinute(3)->by($userId.'|'.$request->ip()),
                Limit::perMinute(10)->by('user-verification|'.$request->ip()),
            ];
        });

        RateLimiter::for('user-password-forgot', function (Request $request): array {
            $key = $this->emailAndIpKey($request);

            return [
                Limit::perMinute(3)->by($key),
                Limit::perMinute(10)->by('user-password-forgot|'.$request->ip()),
            ];
        });

        RateLimiter::for('user-password-reset', function (Request $request): array {
            $key = $this->emailAndIpKey($request);

            return [
                Limit::perMinute(5)->by($key),
                Limit::perMinute(20)->by('user-password-reset|'.$request->ip()),
            ];
        });

        RateLimiter::for('expert-registration', function (Request $request): array {
            $key = $this->emailAndIpKey($request);

            return [
                Limit::perMinute(5)->by($key),
                Limit::perMinute(20)->by('expert-registration|'.$request->ip()),
            ];
        });

        RateLimiter::for('expert-login', function (Request $request): array {
            $key = $this->emailAndIpKey($request);

            return [
                Limit::perMinute(5)->by($key),
                Limit::perMinute(30)->by('expert-login|'.$request->ip()),
            ];
        });

        RateLimiter::for('expert-verification', function (Request $request): array {
            $expertId = $request->user()?->getAuthIdentifier() ?? 'guest';

            return [
                Limit::perMinute(3)->by($expertId.'|'.$request->ip()),
                Limit::perMinute(10)->by('expert-verification|'.$request->ip()),
            ];
        });

        RateLimiter::for('expert-password-forgot', function (Request $request): array {
            $key = $this->emailAndIpKey($request);

            return [
                Limit::perMinute(3)->by($key),
                Limit::perMinute(10)->by('expert-password-forgot|'.$request->ip()),
            ];
        });

        RateLimiter::for('expert-password-reset', function (Request $request): array {
            $key = $this->emailAndIpKey($request);

            return [
                Limit::perMinute(5)->by($key),
                Limit::perMinute(20)->by('expert-password-reset|'.$request->ip()),
            ];
        });

        RateLimiter::for('expert-kyc-write', fn (Request $request): Limit => Limit::perMinute(30)
            ->by($this->authenticatedKey($request)));

        RateLimiter::for('expert-kyc-upload', fn (Request $request): Limit => Limit::perMinute(15)
            ->by($this->authenticatedKey($request)));

        RateLimiter::for('expert-kyc-submit', fn (Request $request): Limit => Limit::perMinute(5)
            ->by($this->authenticatedKey($request)));

        RateLimiter::for('admin-kyc-decisions', fn (Request $request): Limit => Limit::perMinute(30)
            ->by($this->authenticatedKey($request)));
    }

    private function emailAndIpKey(Request $request): string
    {
        $email = mb_strtolower(trim((string) $request->input('email')));

        return $email.'|'.$request->ip();
    }

    private function authenticatedKey(Request $request): string
    {
        return ($request->user()?->getAuthIdentifier() ?? 'guest').'|'.$request->ip();
    }
}
