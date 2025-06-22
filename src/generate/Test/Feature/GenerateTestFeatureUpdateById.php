<?php

namespace Lauchoit\LaravelHexMod\generate\Test\Feature;

use Illuminate\Support\Str;
use Lauchoit\LaravelHexMod\generate\Shared\ValidFieldType;

class GenerateTestFeatureUpdateById
{
    public static function run(array $fields, string $content, string $camelName, string $studlyName, string $snakeName): string
    {
        $filtered = collect($fields)->reject(fn($field) => in_array(explode(':', $field)[0], ['id', 'createdAt', 'updatedAt']));
        $filteredJsonFields = collect($fields)
            ->filter(function ($field) {
                [, $type] = explode(':', $field);
                return $type === ValidFieldType::JSON->value;
            })
            ->values();

        $updatedData = $filtered->map(function ($field, $i) {
            [$name, $type] = explode(':', $field);
            $camelName = Str::camel($name);
            $value = self::exampleUpdateValue($type, $i);
            return "            '" . $camelName . "' => " . var_export($value, true) . ",";
        })->implode("\n");

        $snakeNameData = $filtered->map(function ($field, $i) {
            [$name, $type] = explode(':', $field);
            if ($type !== ValidFieldType::JSON->value) {
                $value = self::exampleUpdateValue($type, $i);
                $snakeNameDatabase = Str::snake($name);
                return "            '" . $snakeNameDatabase . "' => " . var_export($value, true) . ",";
            }
        })->filter()->implode("\n");

        // 🔧 JSON assertions bien alineadas
        $jsonAssertions = '';
        foreach ($filteredJsonFields as $key => $field) {
            $indent = $key === 0 ? '': str_repeat(' ', 8);
            [$name, $type] = explode(':', $field);
            if ($type === ValidFieldType::JSON->value) {
                echo("Processing JSON field: {$key}\n");
                $camel = Str::camel($name);
                $jsonAssertions .= "{$indent}\$metadata = json_decode(\$data['{$camel}'], true, 512, JSON_THROW_ON_ERROR);\n";
                $jsonAssertions .= "        \$this->assertEquals(['updated' => true], \$metadata);\n";
            }
        }

        $databaseData = $filtered->map(function ($field, $i) {
            [$name, $type] = explode(':', $field);
            $snakeName = Str::snake($name);
            $value = self::exampleUpdateValue($type, $i);
            return "            '" . $snakeName . "' => " . var_export($value, true) . ",";
        })->implode("\n");

        $randomField = $filtered->random();
        [$onlyOneKey, $onlyOneType] = explode(':', $randomField);
        $onlyOneValue = self::exampleUpdateValue($onlyOneType, 1);
        $onlyOneFieldData = "            '" . $onlyOneKey . "' => " . var_export($onlyOneValue, true) . ",";

        $assertDatabaseHasPartial = "            'id' => \${$camelName}->id,\n";
        $filtered->each(function ($field) use (&$assertDatabaseHasPartial, $onlyOneKey, $onlyOneValue, $camelName) {
            [$name, $type] = explode(':', $field);
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
            return "                '" . $camelName . "',";
        })->prepend("                'id',")
            ->push("                'createdAt',")
            ->push("                'updatedAt',")
            ->implode("\n");

        // ✅ Alineación de assertions JSON y base de datos en la prueba de un solo campo
        $assertDatabaseHasPartialOnlyOneField = "            'id' => \${$camelName}->id,\n";
        $jsonAssertionsOnlyOneField = '';

        $filteredJsonFields->each(function ($field, $key) use (&$assertDatabaseHasPartialOnlyOneField, &$jsonAssertionsOnlyOneField, $onlyOneKey, $onlyOneValue, $camelName) {
            $indent = $key === 0 ? '': str_repeat(' ', 8);
            [$name, $type] = explode(':', $field);
            $snake = Str::snake($name);
            $camel = Str::camel($name);

            if ($type === ValidFieldType::JSON->value) {
                if ($name === $onlyOneKey) {
                    $jsonAssertionsOnlyOneField .= "{$indent}\$metadata = json_decode(\$data['{$camel}'], true, 512, JSON_THROW_ON_ERROR);\n";
                    $jsonAssertionsOnlyOneField .= "        \$this->assertEquals(['updated' => true], \$metadata);\n";
                } else {
                    $jsonAssertionsOnlyOneField .= "{$indent}\$metadata = json_decode(\${$camelName}->{$camel}, true, 512, JSON_THROW_ON_ERROR);\n";
                    $jsonAssertionsOnlyOneField .= "        \$this->assertEquals(\$metadata, json_decode(\${$camelName}->{$camel}, true));\n";
                }
                return;
            }

            if ($name === $onlyOneKey) {
                $assertDatabaseHasPartialOnlyOneField .= "            '" . $snake . "' => " . var_export($onlyOneValue, true) . ",\n";
            } else {
                $assertDatabaseHasPartialOnlyOneField .= "            '" . $snake . "' => \${$camelName}->{$name},\n";
            }
        });

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
                '{{jsonAssertions}}',
                '{{assertDatabaseHasPartialOnlyOneField}}',
                '{{jsonAssertionsOnlyOneField}}',
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
                $jsonAssertions,
                $assertDatabaseHasPartialOnlyOneField,
                $jsonAssertionsOnlyOneField,
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
