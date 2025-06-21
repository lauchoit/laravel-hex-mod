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
            $value = self::exampleUpdateValue($type, $i);
            return "            '" . $name . "' => " . var_export($value, true) . ",";
        })->implode("\n");

        // Obtener un campo aleatorio
        $randomField = $filtered->random();
        [$onlyOneKey, $onlyOneType] = explode(':', $randomField);
        $onlyOneValue = self::exampleUpdateValue($onlyOneType, 1);
        $onlyOneFieldData = "            '" . $onlyOneKey . "' => " . var_export($onlyOneValue, true) . ",";

        $assertDatabaseHasPartial = "            'id' => \${$camelName}->id,\n";
        $filtered->each(function ($field) use (&$assertDatabaseHasPartial, $onlyOneKey, $onlyOneValue, $camelName) {
            $name = explode(':', $field)[0];
            if ($name !== $onlyOneKey) {
                $assertDatabaseHasPartial .= "            '" . $name . "' => \${$camelName}->{$name},\n";
            } else {
                $assertDatabaseHasPartial .= "            '" . $name . "' => " . var_export($onlyOneValue, true) . ",\n";
            }
        });

        $assertDatabaseMissing = "            'id' => \${$camelName}->id,\n";
        $assertDatabaseMissing .= "            '" . $onlyOneKey . "' => " . var_export($onlyOneValue, true) . ",\n";

        $jsonFields = $filtered->map(function ($field) {
            return "                '" . explode(':', $field)[0] . "',";
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
            'date', 'datetime' => '2025-07-01',
            'json' => ['updated' => true],
            default => 'Updated',
        };
    }
}
