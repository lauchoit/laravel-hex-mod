<?php

namespace Lauchoit\LaravelHexMod\generate\Test\Feature;

use Illuminate\Support\Str;

class GenerateTestFeatureUpdateById
{
    public static function run(array $fields, string $content, string $camelName, string $studlyName, string $snakeName): string
    {
        $filtered = collect($fields)->reject(fn($field) => in_array(explode(':', $field)[0], ['id', 'createdAt', 'updatedAt']));

        $updatedData = $filtered->map(function ($field, $i) {
            [$name, $type] = explode(':', $field);
            $camelName = Str::camel($name);
            $value = self::exampleUpdateValue($type, $i);
            return "            '" . $camelName . "' => " . var_export($value, true) . ",";
        })->implode("\n");

        // Usar camelCase para request
        $snakeNameData = $filtered->map(function ($field, $i) {
            [$name, $type] = explode(':', $field);
            $snakeNameDatabase = Str::snake($name);
            $value = self::exampleUpdateValue($type, $i);
            return "            '" . $snakeNameDatabase . "' => " . var_export($value, true) . ",";
        })->implode("\n");

        // Database assertion: snake_case keys
        $databaseData = $filtered->map(function ($field, $i) {
            [$name, $type] = explode(':', $field);
            $snakeName = Str::snake($name);
            $value = self::exampleUpdateValue($type, $i);
            return "            '" . $snakeName . "' => " . var_export($value, true) . ",";
        })->implode("\n");

        // Obtener un campo aleatorio
        $randomField = $filtered->random();
        [$onlyOneKey, $onlyOneType] = explode(':', $randomField);
        $onlyOneValue = self::exampleUpdateValue($onlyOneType, 1);
        $onlyOneFieldData = "            '" . $onlyOneKey . "' => " . var_export($onlyOneValue, true) . ",";

        $assertDatabaseHasPartial = "            'id' => \${$camelName}->id,\n";
        $filtered->each(function ($field) use (&$assertDatabaseHasPartial, $onlyOneKey, $onlyOneValue, $camelName) {
            $name = explode(':', $field)[0];
            $snake = Str::snake($name);
            if ($name !== $onlyOneKey) {
                $assertDatabaseHasPartial .= "            '" . $snake . "' => \${$camelName}->{$name},\n";
            } else {
                $assertDatabaseHasPartial .= "            '" . $snake . "' => " . var_export($onlyOneValue, true) . ",\n";
            }
        });

        $assertDatabaseMissing = "            'id' => \${$camelName}->id,\n";
        $assertDatabaseMissing .= "            '" . Str::snake($onlyOneKey) . "' => " . var_export($onlyOneValue, true) . ",\n";

        $jsonFields = $filtered->map(function ($field) {
            $camelName = Str::camel(explode(':', $field)[0]);
            return "                '" .$camelName . "',";
        })->prepend("                'id',")
            ->push("                'createdAt',")
            ->push("                'updatedAt',")
            ->implode("\n");

        return str_replace(
            [
                '{{camelName}}',
                '{{StudlyName}}',
                '{{updatedData}}',
                '{{onlyOneFieldData}}',
                '{{assertDatabaseHasPartial}}',
                '{{assertDatabaseMissing}}',
                '{{jsonFields}}',
                '{{snakeName}}',
                '{{databaseData}}',
                '{{snakeNameData}}',
            ],
            [
                $camelName,
                $studlyName,
                $updatedData,
                $onlyOneFieldData,
                $assertDatabaseHasPartial,
                $assertDatabaseMissing,
                $jsonFields,
                $snakeName,
                $databaseData,
                $snakeNameData,
            ],
            $content
        );
    }

    private static function exampleUpdateValue(string $type, int $index): mixed
    {
        return match ($type) {
            'string', 'text', 'longText' => "Updated Value " . ($index + 1),
            'integer' => 99 + $index,
            'float' => 99.99 + $index,
            'boolean' => $index % 2 === 0,
            'date' => '2025-07-01',
            'datetime' => '2025-07-01 12:00:00',
            'json' => json_encode(['updated' => true]),
            default => 'Updated',
        };
    }
}
