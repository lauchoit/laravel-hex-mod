<?php

namespace Lauchoit\LaravelHexMod\generate\Test\Feature;

use Illuminate\Support\Str;

class GenerateTestFeatureCreate
{
    public static function run(array $fields, string $content, string $camelName, string $kebabName, string $studlyName): string
    {
        $filtered = collect($fields)->reject(fn($field) => in_array(explode(':', $field)[0], ['id', 'createdAt', 'updatedAt']));

        // CamelCase para payload JSON
        $validCamel = $filtered->mapWithKeys(function ($field) {
            [$name, $type] = explode(':', $field);
            $value = self::exampleValue($type, $name);

            if ($type === 'date') {
                // Ajustamos al formato ISO retornado por Laravel
                $value .= 'T00:00:00.000000Z';
            }

            return [Str::camel($name) => $value];
        })->all();
        $validData = self::toPhpArrayString($validCamel, 2);

        // SnakeCase para assertDatabaseHas
        $validSnake = $filtered->mapWithKeys(function ($field) {
            [$name, $type] = explode(':', $field);
            return [Str::snake($name) => self::exampleValue($type, $name)];
        })->all();
        $databaseData = self::toPhpArrayString($validSnake, 3);

        // Campos vacíos camelCase
        $invalidCamel = $filtered->mapWithKeys(function ($field) {
            return [Str::camel(explode(':', $field)[0]) => ''];
        })->all();
        $invalidData = self::toPhpArrayString($invalidCamel, 2);

        // Fragmento esperado de validaciones
        $invalidFragment = $filtered->mapWithKeys(function ($field) {
            $original = explode(':', $field)[0];
            $camel = Str::camel($original);
            $label = str_replace('_', ' ', $original);
            return [$camel => ["The {$label} field is required."]];
        })->all();
        $invalidFragmentString = self::toPhpArrayString($invalidFragment, 2);

        // Json response structure (con camelCase)
        $jsonFields = $filtered->map(function ($field) {
            return "                     '" . Str::camel(explode(':', $field)[0]) . "',";
        })
            ->push("                     'createdAt',")
            ->push("                     'updatedAt',")
            ->implode("\n");

        return str_replace(
            ['{{camelName}}', '{{kebabName}}', '{{StudlyName}}', '{{validData}}', '{{invalidData}}', '{{invalidFragment}}', '{{jsonFields}}', '{{databaseData}}'],
            [$camelName, $kebabName, $studlyName, $validData, $invalidData, $invalidFragmentString, $jsonFields, $databaseData],
            $content
        );
    }


    private static function exampleValue(string $type, string $name): mixed
    {
        return match ($type) {
            'string', 'text', 'longText' => ucfirst($name),
            'integer' => 1,
            'float' => 123.45,
            'boolean' => true,
            'date', 'datetime' => '2025-07-01',
            'json' => ['key' => 'value'],
            default => 'value'
        };
    }

    private static function toPhpArrayString(array $array, int $indentLevel = 1): string
    {
        $indent = str_repeat(' ', 4 * $indentLevel);
        $baseIndent = str_repeat(' ', 4 * ($indentLevel - 1));
        $arrayStr = "[\n";

        foreach ($array as $key => $value) {
            $keyStr = var_export($key, true);
            if (is_array($value)) {
                $nested = self::toPhpArrayString($value, $indentLevel + 1);
                $arrayStr .= "{$indent}{$keyStr} => {$nested},\n";
            } else {
                $valueStr = var_export($value, true);
                $arrayStr .= "{$indent}{$keyStr} => {$valueStr},\n";
            }
        }

        $arrayStr .= "{$baseIndent}]";
        return $arrayStr;
    }
}
