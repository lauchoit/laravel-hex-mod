<?php

namespace Lauchoit\LaravelHexMod\generate\Test\Feature;

use Illuminate\Support\Str;

class GenerateTestFeatureFindAll
{
    public static function run(array $fields, string $content, string $camelName, string $studlyName): string
    {
        // Campos a excluir del JSON principal
        $excluded = ['id', 'created_at', 'updated_at'];

        // JsonStructure keys (camelCase)
        $structureFields = collect($fields)
            ->reject(fn($field) => in_array(explode(':', $field)[0], $excluded))
            ->map(fn($field) => "                    '" . Str::camel(explode(':', $field)[0]) . "',")
            ->prepend("                    'id',")
            ->push("                    'createdAt',")
            ->push("                    'updatedAt',")
            ->implode("\n");

        return str_replace(
            ['{{StudlyName}}', '{{camelName}}', '{{jsonFields}}'],
            [$studlyName, $camelName, $structureFields],
            $content
        );
    }
}