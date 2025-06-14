<?php

namespace Lauchoit\LaravelHexMod\generate;

use Illuminate\Support\Str;

class GenerateEntity
{
    /**
     * @param array $fields
     * @param array|bool|string $content
     * @return string
     */
    public static function run(array $fields, array|bool|string $content): string
    {
        $timestampFields = ['createdAt:string', 'updatedAt:string'];
        $fields = array_merge($fields, $timestampFields);

        // Properties with docblocks
        $properties = collect([]);
        $properties->push(self::generateDocProperty('id', 'int'));
        foreach ($fields as $field) {
            [$name, $type] = explode(':', $field);
            $name = Str::camel($name);
            $phpType = self::mapPhpType($type);
            $properties->push(self::generateDocProperty($name, $phpType));
        }
        $propertiesBlock = $properties->implode("\n\n");

        // Constructor parameters
        $constructorParams = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            $name = Str::camel($name);
            return self::mapPhpType($type) . " \${$name}";
        })->prepend("int \$id")->implode(', ');

        // Constructor body
        $constructorBody = collect($fields)->map(function ($field) {
            $name = explode(':', $field)[0];
            $name = Str::camel($name);
            return "        \$this->{$name} = \${$name};";
        })->prepend("        \$this->id = \$id;")->implode("\n");

        // Getters & Setters
        $gettersSetters = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            $name = Str::camel($name);
            $ucName = Str::studly($name);
            $phpType = self::mapPhpType($type);

            return <<<EOT

    /**
     * @return {$phpType}
     */
    public function get{$ucName}(): {$phpType}
    {
        return \$this->{$name};
    }

    /**
     * @param {$phpType} \${$name}
     * @return void
     */
    public function set{$ucName}({$phpType} \${$name}): void
    {
        \$this->{$name} = \${$name};
    }
EOT;
        })->prepend(<<<EOT

    public function getId(): int
    {
        return \$this->id;
    }
EOT
        )->implode("\n");

        $content = str_replace(
            ['{{properties}}', '{{constructorParams}}', '{{constructorBody}}', '{{gettersSetters}}'],
            [$propertiesBlock, $constructorParams, $constructorBody, $gettersSetters],
            $content
        );

        return $content;
    }

    private static function mapPhpType(string $type): string
    {
        return match ($type) {
            'string', 'text', 'longText', 'json', 'date', 'datetime' => 'string',
            'integer' => 'int',
            'float' => 'float',
            'boolean' => 'bool',
            default => 'mixed',
        };
    }

    private static function generateDocProperty(string $name, string $phpType): string
    {
        return <<<EOL
    /**
     * @var {$phpType}
     */
    private {$phpType} \${$name};
EOL;
    }
}
