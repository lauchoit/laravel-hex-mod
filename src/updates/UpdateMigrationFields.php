<?php

namespace Lauchoit\LaravelHexMod\updates;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class UpdateMigrationFields
{
    public static function run(Command $command, string $studlyName, array $fields): void
    {
        $table = Str::snake(Str::pluralStudly($studlyName));

        $migrationFile = collect(File::files(database_path('migrations')))
            ->filter(fn($file) => str_contains($file->getFilename(), "create_{$table}_table"))
            ->first();

        if (!$migrationFile) {
            $command->warn("⚠️ No se encontró la migración para {$table}");
            return;
        }

        $migrationContent = File::get($migrationFile->getPathname());

        $columnLines = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            return match ($type) {
                'string' => "\$table->string('$name');",
                'text' => "\$table->text('$name');",
                'integer' => "\$table->integer('$name');",
                'decimal' => "\$table->decimal('$name', 8, 2);",
                'boolean' => "\$table->boolean('$name');",
                default => "\$table->$type('$name');",
            };
        })->implode("\n            ");

        $migrationContent = preg_replace(
            '/\$table->id\(\);\n/',
            "\$table->id();\n            {$columnLines}\n",
            $migrationContent
        );

        File::put($migrationFile->getPathname(), $migrationContent);
        $command->info("✅ Migración actualizada con campos: {$table}");
    }

}