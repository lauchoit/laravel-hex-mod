<?php

namespace Lauchoit\LaravelHexMod\inject;

use Illuminate\Console\Command;

class InjectModulesRoutes
{

    public static function run(Command $command, $studlyName, $kebabName): void
    {
        $bootstrapPath = base_path('bootstrap/app.php');

        if (!file_exists($bootstrapPath)) {
            $command->warn('⚠️ No se encontró bootstrap/app.php');
            return;
        }

        $routeEntry = "require base_path('src/{$kebabName}/infrastructure/Routes/{$studlyName}Routes.php'),";

        $file = file_get_contents($bootstrapPath);

        // Ya está incluido
        if (str_contains($file, $routeEntry)) {
            $command->info("🔁 Rutas de {$studlyName} ya están registradas.");
            return;
        }

        // Si ya existe el bloque then: fn () => [ ... ]
        if (preg_match('/then:\s*fn\s*\(\)\s*=>\s*\[\s*(.*?)\]/s', $file, $matches)) {
            // Insertar justo antes del cierre del array
            $updatedBlock = rtrim($matches[1]) . "\n            {$routeEntry}";
            $file = preg_replace(
                '/then:\s*fn\s*\(\)\s*=>\s*\[\s*.*?\]/s',
                "then: fn () => [\n            {$updatedBlock}\n        ]",
                $file
            );
            $command->info("✅ Ruta agregada a bloque existente: {$routeEntry}");
        } else {
            // Agregar nuevo bloque `then: fn () => [ ... ]`
            $file = preg_replace_callback(
                '/withRouting\((.*?)\)/s',
                fn ($matches) => "withRouting({$matches[1]}\n        then: fn () => [\n            {$routeEntry}\n        ])",
                $file
            );
            $command->info("✅ Bloque then creado con primera ruta: {$routeEntry}");
        }

        file_put_contents($bootstrapPath, $file);
    }

}