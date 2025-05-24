<?php

namespace Lauchoit\LaravelHexMod\generate;

class GenerateRequest
{
    /**
     * @param array $fields
     * @param array|bool|string $content
     * @return array|bool|string|string[]
     */
    public static function run(array $fields, array|bool|string $content): string|array|bool
    {
        $rules = collect($fields)->map(function ($field) {
            $name = explode(':', $field)[0];
            return "            '{$name}' => 'required',";
        })->implode("\n");

        $rulesWrapped = "return [\n" . $rules . "\n        ];";

        $content = str_replace('{{validationRules}}', $rulesWrapped, $content);
        return $content;
    }
}