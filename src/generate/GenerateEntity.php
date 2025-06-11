<?php

namespace Lauchoit\LaravelHexMod\generate;

use Illuminate\Support\Str;
class GenerateEntity
{
    /**
     * @param array $fields
     * @param array|bool|string $content
     * @return array|bool|string|string[]
     */
    public static function run(array $fields, array|bool|string $content): string
    {
        $timestampFields = ['createdAt:datetime', 'updatedAt:datetime'];
        $fields = array_merge($fields, $timestampFields);
        $properties = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            return "    private ".self::mapPhpType($type)." \${$name};";
        })->prepend("    private int \$id;")->implode("\n");

        $constructorParams = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            return self::mapPhpType($type) . " \${$name}";
        })->prepend("int \$id")->implode(', ');

        $constructorBody = collect($fields)->map(function ($field) {
            $name = explode(':', $field)[0];
            return "        \$this->{$name} = \${$name};";
        })->prepend("        \$this->id = \$id;")->implode("\n");

        $gettersSetters = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            $ucName = Str::studly($name);
            $phpType = self::mapPhpType($type);

            return <<<EOT

                        public function get{$ucName}(): {$phpType}
                        {
                            return \$this->{$name};
                        }
                    
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
            [$properties, $constructorParams, $constructorBody, $gettersSetters],
            $content
        );
        return $content;

    }

    private static function mapPhpType(string $type): string
    {
        return match ($type) {
            'string', 'text', 'longText', 'json' => 'string',
            'integer' => 'int',
            'float' => 'float',
            'date', 'datetime' => '\Carbon\Carbon',
            'boolean' => 'bool',
            default => 'mixed',
        };
    }
}