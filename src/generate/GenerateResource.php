<?php

namespace Lauchoit\LaravelHexMod\generate;

use Illuminate\Support\Str;

class GenerateResource
{
    /**
     * @param array $fields
     * @param array|bool|string $content
     * @param string $kebabName
     * @return string
     */
    public static function run(array $fields, array|bool|string $content, string $kebabName): string
    {
        $resourceFields = collect($fields)->map(function ($field) use ($kebabName) {
            $name = explode(':', $field)[0];
            $method = 'get' . Str::studly($name);
            return "            '{$name}' => \${$kebabName}->{$method}(),";
        })->prepend("'id' => \${$kebabName}->getId(),")
            ->add("            'createdAt' => \${$kebabName}->getCreatedAt(),")
            ->add("            'updatedAt' => \${$kebabName}->getUpdatedAt(),")
            ->implode("\n");

        $content = str_replace('{{resourceFields}}', $resourceFields, $content);
        return $content;
    }
}
