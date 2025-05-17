<?php

namespace Lauchoit\LaravelHexMod;

use Illuminate\Support\ServiceProvider;

class LaravelHexModProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeHexModCommand::class,
            ]);
        }
    }
}