<?php

namespace Lauchoit\LaravelHexMod\generate\Test\Feature;

use Illuminate\Support\Str;

class GenerateTestFeatureFindById
{
    public static function run(array $fields, string $content, string $camelName, string $studlyName): string
    {
        // Campos excluidos del JSON que ya están hardcodeados
        $excluded = ['id', 'created_at', 'updated_at'];

        // Construimos las líneas de campos en camelCase
        $jsonFields = collect($fields)
            ->reject(fn($field) => in_array(explode(':', $field)[0], $excluded))
            ->map(fn($field) => "                '" . Str::camel(explode(':', $field)[0]) . "',")
            ->prepend("                'id',")
            ->push("                'createdAt',")
            ->push("                'updatedAt',")
            ->implode("\n");

        return str_replace(
            ['{{StudlyName}}', '{{camelName}}', '{{jsonFields}}'],
            [$studlyName, $camelName, $jsonFields],
            $content
        );
    }
}
