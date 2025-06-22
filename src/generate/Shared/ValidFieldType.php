<?php

namespace Lauchoit\LaravelHexMod\generate\Shared;

enum ValidFieldType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case JSON = 'json';
    case BOOLEAN = 'boolean';
    case TEXT = 'text';
    case LONGTEXT = 'longText';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, self::values(), true);
    }
    public static function errorMessage(string $invalidType): string
    {
        return "❌ Invalid field type '{$invalidType}'. Valid types are: " . implode(', ', self::values()) . ".";
    }
}