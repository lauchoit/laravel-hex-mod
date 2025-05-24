<?php

namespace Lauchoit\LaravelHexMod\generate;

use Illuminate\Console\Command;

interface BaseGenerate
{
    public static function run(Command $command): void;

}