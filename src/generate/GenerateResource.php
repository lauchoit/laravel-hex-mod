<?php

namespace Lauchoit\LaravelHexMod\generate;

use Illuminate\Support\Str;
class GenerateResource
{

    /**
     * @param array $fields
     * @param array|bool|string $content
     * @return array|bool|string|string[]
     */
    public static function run(array $fields, array|bool|string $content): string
    {
        $resourceFields = collect($fields)->map(function ($field) {
            $name = explode(':', $field)[0];
            $method = 'get' . Str::studly($name);
            return "            '{$name}' => \$this->{$method}(),";
        })->prepend("'id' => \$this->getId(),")
            ->add("            'createdAt' => \$this->getCreatedAt(),")
            ->add("            'updatedAt' => \$this->getUpdatedAt(),")
            ->implode("\n");

        $content = str_replace('{{resourceFields}}', $resourceFields, $content);
        return $content;
    }
}