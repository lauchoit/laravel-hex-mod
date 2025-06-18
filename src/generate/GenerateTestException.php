<?php

namespace Lauchoit\LaravelHexMod\generate;

class GenerateTestException
{
    public static function run(string $studlyName, string $content): string
    {
        return str_replace('{{StudlyName}}', $studlyName, $content);
    }
}