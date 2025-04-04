<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
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
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $params = [
                "expires" => Carbon::now()
                    ->addMinutes(60)
                    ->getTimestamp(),
                "id" => $notifiable->getKey(),
                "hash" => sha1($notifiable->getEmailForVerification()),
            ];

            ksort($params);

            // then create API url for verification. my API have `/api` prefix,
            // so I don't want to show that url to users
            $url = \URL::route("verification.verify", $params, true);

            // get APP_KEY from config and create signature
            $key = config("app.key");
            $signature = hash_hmac("sha256", $url, $key);

            // generate url for yous SPA page to send it to user
            return env("FRONTEND_URL") .
                "/auth/verify-email/" .
                $params["id"] .
                "/" .
                $params["hash"] .
                "?expires=" .
                $params["expires"] .
                "&signature=" .
                $signature;
        });

        Fortify::loginView(function () {
            return redirect()->to(getenv('FRONTEND_URL'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
