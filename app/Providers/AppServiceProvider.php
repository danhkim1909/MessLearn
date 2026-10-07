<?php

namespace App\Providers;

use App\Routing\CustomUrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();

            $app->instance('routes', $routes);

            $url = new CustomUrlGenerator(
                $routes,
                $app->rebinding('request', function ($app, $request) {
                    $app['url']->setRequest($request);
                }),
                $app['config']['app.asset_url']
            );

            $url->setSessionResolver(function () use ($app) {
                return $app['session'] ?? null;
            });

            $url->setKeyResolver(function () use ($app) {
                return $app['config']['app.key'];
            });

            return $url;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
