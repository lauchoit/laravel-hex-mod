<?php

namespace Lauchoit\LaravelHexMod\generate;

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
            $mapperFields->push(self::getIdent(3) . "{$name}: \${$camelName}->{$name},");
        }

        $mapperFields->push(self::getIdent(3) . "createdAt: \${$camelName}->created_at,");
        $mapperFields->push(self::getIdent(3) . "updatedAt: \${$camelName}->updated_at,");

        $mapperBlock = $mapperFields->implode("\n");

        // toPersistence
        $persistenceFields = collect([]);

        foreach ($fields as $key => $field) {
            $name = explode(':', $field)[0];
            if ($key === 0) {
                $persistenceFields->push(self::getIdent(2) . "'{$name}' => \$data['{$name}'] ?? null,");
            } else {
                $persistenceFields->push(self::getIdent(3) . "'{$name}' => \$data['{$name}'] ?? null,");
            }
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
