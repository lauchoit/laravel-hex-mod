<?php

namespace Lauchoit\LaravelHexMod\generate;

class GenerateModel
{
    public static function run(string $studlyName, array $fields, string $stub): string
    {
        // phpDocBlock
        $docLines = [
            " * @property int \$id",
        ];

        // Casts
        $castTypes = ['date', 'datetime', 'boolean', 'hashed'];
        $casts = [];

        foreach ($fields as $field) {
            [$name, $type] = array_pad(explode(':', $field), 2, 'string');
            $docType = match ($type) {
                'integer' => 'int',
                'float' => 'float',
                'boolean' => 'bool',
                'date', 'datetime', 'json', 'text', 'longText' => 'string',
                default => 'string',
            };

            $docLines[] = " * @property {$docType} \${$name}";

            if (in_array($type, $castTypes)) {
                $casts[] = "            '{$name}' => '{$type}',";
            }
        }

        $docLines[] = " * @property string \$created_at";
        $docLines[] = " * @property string \$updated_at";

        $phpDocBlock = "/**\n" . implode("\n", $docLines) . "\n */";

        $casts = array_merge(
            ["            'created_at' => 'datetime',", "            'updated_at' => 'datetime',"],
            $casts
        );
        $castsArray = implode("\n", $casts);

        return str_replace(
            ['{{StudlyName}}', '{{phpDocBlock}}', '{{castsArray}}'],
            [$studlyName, $phpDocBlock, $castsArray],
            $stub
        );
    }
}