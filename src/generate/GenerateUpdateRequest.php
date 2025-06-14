<?php

namespace Lauchoit\LaravelHexMod\generate;

use Illuminate\Support\Str;

class GenerateUpdateRequest
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
            $name = Str::camel($name);
            $rules = '';
            $type = explode(':', $field)[1] ?? 'string';
            if ($type === 'string') {
                $rules .= 'string|max:255';
            } elseif ($type === 'integer') {
                $rules .= 'integer';
            } elseif ($type === 'float') {
                $rules .= 'numeric';
            } elseif ($type === 'date') {
                $rules .= 'date';
            } elseif ($type === 'datetime') {
                $rules .= 'date_format:Y-m-d H:i:s';
            } elseif ($type === 'json') {
                $rules .= 'json';
            } elseif ($type === 'boolean') {
                $rules .= 'boolean';
            } elseif ($type === 'text') {
                $rules .= 'string';
            } elseif ($type === 'longText') {
                $rules .= 'string';
            }
            return "            '{$name}' => '{$rules}',";
        })->implode("\n");

        $rulesWrapped = "return [\n" . $rules . "\n        ];";

        $content = str_replace('{{validationRules}}', $rulesWrapped, $content);
        return $content;
    }
}