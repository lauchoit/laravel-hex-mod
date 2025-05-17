<?php

namespace Lauchoit\LaravelHexMod;

use Illuminate\Support\ServiceProvider;
use LauchoIT\LaravelService\MakeHexModCommand;

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