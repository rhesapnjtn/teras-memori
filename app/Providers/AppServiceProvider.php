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
        /*
        |--------------------------------------------------------------------------
        | Public Chat Rate Limiting
        |--------------------------------------------------------------------------
        */

        RateLimiter::for(
            'public-chat-lookup',
            function (Request $request) {
                return Limit::perMinute(20)
                    ->by(
                        $request->ip()
                        . '|'
                        . strtolower(
                            trim(
                                (string) $request->input('email')
                            )
                        )
                    );
            }
        );

        RateLimiter::for(
            'public-chat-create',
            function (Request $request) {
                return Limit::perMinute(10)
                    ->by(
                        $request->ip()
                        . '|'
                        . strtolower(
                            trim(
                                (string) $request->input('email')
                            )
                        )
                    );
            }
        );

        RateLimiter::for(
            'public-chat-customer',
            function (Request $request) {
                return Limit::perMinute(20)
                    ->by(
                        $request->ip()
                        . '|'
                        . strtolower(
                            trim(
                                (string) $request->input('email')
                            )
                        )
                    );
            }
        );

        RateLimiter::for(
            'public-chat-show',
            function (Request $request) {
                return Limit::perMinute(30)
                    ->by(
                        $request->ip()
                        . '|'
                        . $request->header('X-Chat-Token')
                    );
            }
        );

        RateLimiter::for(
            'public-chat-message',
            function (Request $request) {
                return Limit::perMinute(20)
                    ->by(
                        $request->ip()
                        . '|'
                        . $request->header('X-Chat-Token')
                    );
            }
        );
    }
}