<?php

namespace Lauchoit\LaravelHexMod\generate;

use Illuminate\Support\Str;
class GenerateMapper
{
    /**
     * @param array $fields
     * @param $camelName
     * @param array|bool|string $content
     * @return string
     */
    public static function run(array $fields, $camelName, array|bool|string $content): string
    {
        // toDomain
        $mapperFields = collect([]);
        $mapperFields->push("id: \${$camelName}->id,");

        foreach ($fields as $field) {
            $name = explode(':', $field)[0];
            $camelField = Str::camel($name);
            $mapperFields->push(self::getIdent(3) . "{$camelField}: \${$camelName}->{$name},");
        }

        $mapperFields->push(self::getIdent(3) . "createdAt: \${$camelName}->created_at,");
        $mapperFields->push(self::getIdent(3) . "updatedAt: \${$camelName}->updated_at,");

        $mapperBlock = $mapperFields->implode("\n");

        // toPersistence
        $persistenceFields = collect([]);

        foreach ($fields as $field) {
            $name = explode(':', $field)[0];
            $camelField = Str::camel($name);
            $persistenceFields->push(self::getIdent(2) . "\$model->{$name} = \$data['{$camelField}'] ?? \$model->{$name};");
        }

        $persistenceBlock = $persistenceFields->implode("\n");

        return str_replace(
            ['{{mapperFields}}', '{{persistenceFields}}'],
            [$mapperBlock, $persistenceBlock],
            $content
        );
    }

    private static function getIdent(int $level): string
    {
        return str_repeat(' ', $level * 4);
    }
}
