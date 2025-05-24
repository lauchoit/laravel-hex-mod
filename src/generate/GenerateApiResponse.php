<?php

namespace Lauchoit\LaravelHexMod\generate;

use Illuminate\Console\Command;

class GenerateApiResponse implements BaseGenerate
{

    public static function run(Command $command): void
    {
        $basePath = base_path("src/Shared/Responses/ApiResponse.stub");
        $stubPath = dirname(__DIR__) . '/stubs/shared/Responses/ApiResponse.stub';
        $targetPath = str_replace('.stub', '.php', $basePath);
        if (!file_exists($stubPath)) {
            $command->warn("❌ Stub no encontrado: $stubPath");
        } else {
            $dir = dirname($targetPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                }
            }
            $content = file_get_contents($stubPath);
            file_put_contents($targetPath, $content);
            $command->line("✅ Archivo creado: " . str_replace(base_path() . '/', '', $targetPath));
        }
    }
}