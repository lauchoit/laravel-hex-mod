<?php

namespace Lauchoit\LaravelHexMod\updates;

use Illuminate\Console\Command;

class UpdateModelFillable
{
    public static function run(Command $command, string $studlyName, array $fields): void
    {
        $modelPath = app_path("Models/{$studlyName}.php");
        if (!file_exists($modelPath)) {
            $modelPath = app_path("{$studlyName}.php");
        }

        $fillable = collect($fields)->map(fn($f) => "'" . explode(':', $f)[0] . "'")->implode(', ');
        $file = file_get_contents($modelPath);

        $file = preg_replace(
            '/{/',
            "{\n    protected \$fillable = [{$fillable}];\n",
            $file,
            1
        );

        file_put_contents($modelPath, $file);
        $command->info("✅ Fillable actualizado en el modelo {$studlyName}.");
    }
}