<?php

namespace Lauchoit\LaravelHexMod;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class MakeHexModCommand extends Command
{
    protected $signature = 'lauchoit:make-module {name}';
    protected $description = 'Genera la estructura hexagonal base para un módulo';

    public function handle(): void
    {
        $name = $this->argument('name');

        $studlyName = Str::studly($name);
        $kebabName  = Str::kebab($name);

        Artisan::call("make:model $studlyName -mf");
        $this->injectBindingInAppServiceProvider($studlyName);
        $this->injectModuleRoutes($studlyName, $kebabName);

        $basePath = base_path("src/{$studlyName}");
        $stubPath = __DIR__ . '/stubs';

        $this->info("Generando módulo {$studlyName} en: {$basePath}");

        $files = collect([
            "Application/UseCases/CreateMyModuleUseCase.stub",
            "Application/UseCases/DeleteByIdMyModuleUseCase.stub",
            "Application/UseCases/FindAllMyModuleUseCase.stub",
            "Application/UseCases/FindByIdMyModuleUseCase.stub",
            "Application/UseCases/UpdateByIdMyModuleUseCase.stub",
            "Domain/Entity/MyModule.stub",
            "Domain/Mappers/MyModuleMapper.stub",
            "Domain/Repository/MyModuleRepository.stub",
            "Infrastructure/Controllers/MyModuleController.stub",
            "Infrastructure/Repository/MyModuleRepositoryImpl.stub",
            "Infrastructure/Repository/UseCases/CreateMyModuleUseCaseImpl.stub",
            "Infrastructure/Repository/UseCases/DeleteByIdMyModuleUseCaseImpl.stub",
            "Infrastructure/Repository/UseCases/FindAllMyModuleUseCaseImpl.stub",
            "Infrastructure/Repository/UseCases/FindByIdMyModuleUseCaseImpl.stub",
            "Infrastructure/Repository/UseCases/UpdateByIdMyModuleUseCaseImpl.stub",
            "Infrastructure/Routes/MyModuleRoutes.stub",
//            "Infrastructure/Model/MyModuleModel.stub",
//            "Infrastructure/Database/Factories/MyModuleFactory.stub",
//            "Infrastructure/Database/Migrations/0001_01_01_000000_create_myModule_table.stub",
        ]);

        foreach ($files as $relativePath) {
            $sourceStub = $stubPath . '/' . $relativePath;
            $relativePath = str_replace(
                ['MyModule', 'my-module'],
                [$studlyName, $kebabName],
                $relativePath
            );

            $targetPath = $basePath . '/' . str_replace('.stub', '.php', $relativePath);

            if (!file_exists($sourceStub)) {
                $this->warn("❌ Stub no encontrado: $sourceStub");
                continue;
            }

            // Crear carpeta si no existe
            $dir = dirname($targetPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                }
            }

            $content = file_get_contents($sourceStub);
            $content = str_replace(
                ['{{StudlyName}}', '{{kebabName}}'],
                [$studlyName, $kebabName],
                $content
            );

            file_put_contents($targetPath, $content);
            $this->line("✅ Archivo creado: " . str_replace(base_path() . '/', '', $targetPath));
        }

        $this->info("🎉 Módulo {$studlyName} generado correctamente.");
    }

    private function injectBindingInAppServiceProvider(string $studlyName): void
    {
        $providerPath = base_path('app/Providers/AppServiceProvider.php');

        if (!file_exists($providerPath)) {
            $this->warn('⚠️ No se encontró AppServiceProvider.');
            return;
        }

        $interfaceName = "{$studlyName}Repository";
        $implName = "{$studlyName}RepositoryImpl";

        $interfaceFQN = "Lauchoit\\LaravelHexMod\\{$studlyName}\\Domain\\Repository\\{$interfaceName}";
        $implFQN = "Lauchoit\\LaravelHexMod\\{$studlyName}\\Infrastructure\\Repository\\{$implName}";

        $bindLine = "\$this->app->bind({$interfaceName}::class, {$implName}::class);";

        $file = file_get_contents($providerPath);

        // 1. Agregar use si no está
        if (!str_contains($file, "use {$interfaceFQN};")) {
            $file = preg_replace(
                '/namespace App\\\\Providers;\n/',
                "namespace App\\Providers;\n\nuse {$interfaceFQN};\nuse {$implFQN};\n",
                $file,
                1
            );
        }

        // 2. Agregar línea dentro del método boot
        if (!str_contains($file, $bindLine)) {
            $file = preg_replace_callback('/public function boot\(\): void\s*\{\s*/', function ($matches) use ($bindLine) {
                return $matches[0] . "\n        " . $bindLine . "\n";
            }, $file);
        } else {
            $this->info("🔁 El binding ya existe.");
        }

        file_put_contents($providerPath, $file);
        $this->info("✅ Binding y use agregado para {$studlyName}.");
    }

    private function injectModuleRoutes(string $studlyName, string $kebabName): void
    {
        $bootstrapPath = base_path('bootstrap/app.php');

        if (!file_exists($bootstrapPath)) {
            $this->warn('⚠️ No se encontró bootstrap/app.php');
            return;
        }

        $routeEntry = "require base_path('src/{$kebabName}/infrastructure/Routes/{$studlyName}Routes.php'),";

        $file = file_get_contents($bootstrapPath);

        // Ya está incluido
        if (str_contains($file, $routeEntry)) {
            $this->info("🔁 Rutas de {$studlyName} ya están registradas.");
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
            $this->info("✅ Ruta agregada a bloque existente: {$routeEntry}");
        } else {
            // Agregar nuevo bloque `then: fn () => [ ... ]`
            $file = preg_replace_callback(
                '/withRouting\((.*?)\)/s',
                fn ($matches) => "withRouting({$matches[1]}\n        then: fn () => [\n            {$routeEntry}\n        ])",
                $file
            );
            $this->info("✅ Bloque then creado con primera ruta: {$routeEntry}");
        }

        file_put_contents($bootstrapPath, $file);
    }

}
