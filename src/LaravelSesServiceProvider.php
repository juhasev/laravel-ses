<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Juhasev\LaravelSes\Commands\SetupSns;

class LaravelSesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/Mocking/Views', 'LaravelSes');

        if (App::environment(['testing','local','build'])) {
            $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        }

        $this->publishes([
           __DIR__.'/Assets' => public_path('laravel-ses'),
        ], 'ses-assets');

        $this->publishes([
            __DIR__.'/Config/laravelses.php' => config_path('laravelses.php')
        ], 'ses-config');

        $this->publishes([
            __DIR__.'/Migrations/' => database_path('migrations')
        ], 'ses-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupSns::class
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
           __DIR__.'/Config/laravelses.php',
            'laravelses'
       );

        $this->registerIlluminateMailer();
    }

    /**
     * Register SesMailer facade
     *
     */
    protected function registerIlluminateMailer()
    {
        $this->app->singleton('SesMailer', function ($app) {

            $symfonyMailer = app('mailer')->getSymfonyTransport();

            $sesConfig = $app->make('config')->get('laravelses');

            try {
                if (method_exists($symfonyMailer, 'setPingThreshold')) {
                    $symfonyMailer->setPingThreshold(
                        (int)Arr::get($sesConfig, 'ping_threshold', 10)
                    );
                }
            } catch (Exception $e) {
                logger("Unable to set ping threshold on Symfony Mailer. ".$e->getMessage());
            }

            try {
                if (method_exists($symfonyMailer, 'setRestartThreshold')) {
                    $symfonyMailer->setRestartThreshold(
                        (int)Arr::get($sesConfig, 'restart_threshold.threshold', 100),
                        (int)Arr::get($sesConfig, 'restart_threshold.sleep', 0)
                    );
                }
            } catch (Exception $e) {
                logger("Unable to set restart threshold on Symfony Mailer. ".$e->getMessage());
            }

            // Once we have created the mailer instance, we will set a container instance
            // on the mailer. This allows us to resolve mailer classes via containers
            // for maximum testability on said classes instead of passing Closures.
            $mailer = new SesMailer(
                'ses-mailer',
                $app['view'],
                $symfonyMailer,
                $app['events']
            );

            if ($app->bound('queue')) {
                $mailer->setQueue($app['queue']);
            }

            $config = $app->make('config')->get('mail');

            // Next we will set all of the global addresses on this mailer, which allows
            // for easy unification of all "from" addresses as well as easy debugging
            // of sent messages since they get be sent into a single email address.
            foreach (['from', 'reply_to', 'to'] as $type) {
                $this->setGlobalAddress($mailer, $config, $type);
            }

            return $mailer;
        });
    }

    /**
     * Set global address
     *
     * @param $mailer
     * @param array $config
     * @param $type
     */
    protected function setGlobalAddress($mailer, array $config, $type)
    {
        $address = Arr::get($config, $type);

        if (is_array($address) && isset($address['address'])) {
            $mailer->{'always'.Str::studly($type)}($address['address'], $address['name']);
        }
    }
}
