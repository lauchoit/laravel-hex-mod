<?php

namespace Lauchoit\LaravelHexMod\generate;

class GenerateMapper
{
    /**
     * @param array $fields
     * @param $camelName
     * @param array|bool|string $content
     * @return array|bool|string|string[]
     */
    public static function run(array $fields, $camelName, array|bool|string $content): string
    {
        $mapperFields = collect($fields)->map(function ($field) use ($camelName) {
            $name = explode(':', $field)[0];
            return "                {$name}: \${$camelName}->{$name},";
        })->prepend("id: \${$camelName}->id,")
            ->implode("\n");

        $content = str_replace('{{mapperFields}}', $mapperFields, $content);
        return $content;
    }
}