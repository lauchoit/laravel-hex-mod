<?php

namespace Lauchoit\LaravelHexMod\generate;

class GenerateMapper
{
    /**
     * @param array $fields
     * @param $kebabName
     * @param array|bool|string $content
     * @return array|bool|string|string[]
     */
    public static function run(array $fields, $kebabName, array|bool|string $content): string
    {
        $mapperFields = collect($fields)->map(function ($field) use ($kebabName) {
            $name = explode(':', $field)[0];
            return "                {$name}: \${$kebabName}->{$name},";
        })->prepend("id: \${$kebabName}->id,")
            ->implode("\n");

        $content = str_replace('{{mapperFields}}', $mapperFields, $content);
        return $content;
    }
}