<?php

namespace Lauchoit\LaravelHexMod\generate;

use Illuminate\Support\Str;

class GenerateResource
{
    /**
     * @param array $fields
     * @param array|bool|string $content
     * @param string $camelName
     * @return string
     */
    public static function run(array $fields, array|bool|string $content, string $camelName): string
    {
        $resourceFields = collect($fields)->map(function ($field) use ($camelName) {
            $name = explode(':', $field)[0];
            $method = 'get' . Str::studly($name);
            return "            '{$name}' => \${$camelName}->{$method}(),";
        })->prepend("'id' => \${$camelName}->getId(),")
            ->add("            'createdAt' => \${$camelName}->getCreatedAt(),")
            ->add("            'updatedAt' => \${$camelName}->getUpdatedAt(),")
            ->implode("\n");

        return str_replace('{{resourceFields}}', $resourceFields, $content);
    }
}
