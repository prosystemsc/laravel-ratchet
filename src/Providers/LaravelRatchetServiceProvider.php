<?php

namespace Prosystemsc\LaravelRatchet\Providers;

use GrahamCampbell\Throttle\ThrottleServiceProvider;
use Illuminate\Support\ServiceProvider;
use Prosystemsc\LaravelRatchet\Console\Commands\RatchetServerCommand;

class LaravelRatchetServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(ThrottleServiceProvider::class);

        $this->app->singleton('command.ratchet.serve', function () {
            return new RatchetServerCommand();
        });

        $this->commands('command.ratchet.serve');

        $this->mergeConfigFrom(__DIR__ . '/../config/ratchet.php', 'ratchet');
    }

    /**
     * Register routes, translations, views and publishers.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/ratchet.php' => config_path('ratchet.php'),
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['command.ratchet.serve'];
    }
}
