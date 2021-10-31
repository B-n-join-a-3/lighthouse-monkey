<?php

namespace Bnanan\LighthouseMonkey;

use Illuminate\Support\ServiceProvider;
use Bnanan\LighthouseMonkey\Console\CodeForMeCommand;

class LighthouseMonkeyServiceProvider extends ServiceProvider
{
    const COMMANDS = [
        CodeForMeCommand::class
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/lighthouse-monkey.php', 'lighthouse-monkey');

        if ($this->app->runningInConsole()) {
            $this->commands(self::COMMANDS);
        }
    }
}