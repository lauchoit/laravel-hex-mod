<?php

namespace Lauchoit\LaravelHexMod\generate\Test\Unit;

use Illuminate\Support\Str;

class GenerateTestEntity
{
    public static function run(array $fields, string $content, string $camelName): string
    {
        // Aseguramos campos de timestamp al final
        $fields = array_merge($fields, ['createdAt:string', 'updatedAt:string']);

        // Constructor posicional
        $testConstructor = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            return self::exampleValue($type, $name);
        })->prepend('1')->implode(",\n            ");

        // Getters
        $getters = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            $value = self::exampleValue($type, $name);
            $methodName = 'get' . Str::studly(Str::camel($name));
            return "        \$this->assertEquals({$value}, \${{camelName}}->{$methodName}());";
        })->prepend("        \$this->assertEquals(1, \${{camelName}}->getId());")
            ->implode("\n");

        // Setters
        $setters = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            $value = self::updatedValue($type, $name);
            $methodName = 'set' . Str::studly(Str::camel($name));
            return "        \${{camelName}}->{$methodName}({$value});";
        })->implode("\n");

        // Asserts luego del setter
        $asserts = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            $value = self::updatedValue($type, $name);
            $methodName = 'get' . Str::studly(Str::camel($name));
            return "        \$this->assertEquals({$value}, \${{camelName}}->{$methodName}());";
        })->implode("\n");

        // Reemplazos
        $getters = str_replace('{{camelName}}', $camelName, $getters);
        $setters = str_replace('{{camelName}}', $camelName, $setters);
        $asserts = str_replace('{{camelName}}', $camelName, $asserts);

        return str_replace(
            ['{{testConstructor}}', '{{getters}}', '{{setters}}', '{{asserts}}', '{{camelName}}'],
            [$testConstructor, $getters, $setters, $asserts, $camelName],
            $content
        );
    }

    private static function exampleValue(string $type, string $name): string
    {
        return match ($type) {
            'string', 'text', 'longText' => match ($name) {
                'createdAt', 'updatedAt' => "'2024-01-01 00:00:00'",
                default => "'" . ucfirst($name) . "'",
            },
            'integer' => "123",
            'float' => "123.45",
            'boolean' => "true",
            'date', 'datetime' => "'2024-01-01 00:00:00'",
            'json' => "json_encode(['key' => 'value'])",
            default => "null"
        };
    }

    private static function updatedValue(string $type, string $name): string
    {
        return match ($type) {
            'string', 'text', 'longText' => match ($name) {
                'createdAt', 'updatedAt' => "'2024-02-01 00:00:00'",
                default => "'Updated" . ucfirst($name) . "'",
            },
            'integer' => "999",
            'float' => "999.99",
            'boolean' => "false",
            'date', 'datetime' => "'2024-02-01 00:00:00'",
            'json' => "json_encode(['updated' => true])",
            default => "null"
        };
    }

    public static function runSource(array $fields, string $content, string $camelName): string
    {
        $attributeFields = collect($fields)
            ->map(fn($field) => explode(':', $field)[0]) // mantiene snake_case
            ->reject(fn($name) => in_array(Str::snake($name), ['id', 'created_at', 'updated_at']))
            ->map(fn($name) => Str::snake($name)) // fuerza snake_case, por si acaso
            ->values();

        $expectedArray = '[' . $attributeFields->map(fn($f) => "'$f'")->implode(', ') . ']';

        return str_replace(
            ['{{expectedArray}}', '{{camelName}}'],
            [$expectedArray, $camelName],
            $content
        );
    }

}
