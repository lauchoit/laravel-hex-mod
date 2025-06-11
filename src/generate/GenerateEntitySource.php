<?php

namespace Lauchoit\LaravelHexMod\generate;

use Illuminate\Support\Str;
class GenerateEntitySource
{
    /**
     * @param array $fields
     * @param array|bool|string $content
     * @return string
     */
    public static function run(array $fields, array|bool|string $content): string
    {
        $fieldsParams = collect($fields)
            ->map(fn($f) => "'" . explode(':', $f)[0] . "'")
            ->implode(', ');

        $content = str_replace(
            ['{{fieldsParams}}'],
            [$fieldsParams],
            $content
        );

        return $content;
    }
}