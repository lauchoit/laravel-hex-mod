<?php

namespace Lauchoit\LaravelHexMod;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeHexModCommand extends Command
{
    protected $signature = 'lauchoit:make-module {name}';
    protected $description = 'Genera la estructura hexagonal base para un módulo';

    public function handle(): void
    {
        $name = $this->argument('name');

        $studlyName = Str::studly($name);  // Ej: my-module -> MyModule
        $kebabName  = Str::kebab($name);   // Ej: MyModule -> my-module

        $basePath = base_path("modules/{$kebabName}");
        $stubPath = __DIR__ . '/stubs';

        $this->info("Generando módulo {$studlyName} en: {$basePath}");

        $files = collect([
            "application/UseCases/CreateMyModuleUseCase.stub",
            "application/UseCases/DeleteByIdMyModuleUseCase.stub",
            "application/UseCases/FindAllMyModuleUseCase.stub",
            "application/UseCases/FindByIdMyModuleUseCase.stub",
            "application/UseCases/UpdateMyModuleUseCase.stub",
            "domain/Entity/MyModule.stub",
            "domain/Mappers/MyModuleMapper.stub",
            "domain/Repository/MyModuleRepositoty.stub",
            "infrastructure/Controllers/CreateMyModuleController.stub",
            "infrastructure/Controllers/DeleteMyModuleController.stub",
            "infrastructure/Controllers/FindAllMyModuleController.stub",
            "infrastructure/Controllers/FindByIdMyModuleController.stub",
            "infrastructure/Controllers/UpdateMyModuleController.stub",
            "infrastructure/Database/factories/MyModuleFactory.stub",
            "infrastructure/Database/migrations/0001_01_01_000000_create_myModule_table.stub",
            "infrastructure/Model/MyModuleModel.stub",
            "infrastructure/Repository/MyModuleRepositoryImpl.stub",
            "infrastructure/Repository/UserCases/CreateMyModuleUseCaseImpl.stub",
            "infrastructure/Repository/UserCases/DeleteByIdMyModuleUseCaseImpl.stub",
            "infrastructure/Repository/UserCases/FindAllMyModuleUseCaseImpl.stub",
            "infrastructure/Repository/UserCases/FindByIdMyModuleUseCaseImpl.stub",
            "infrastructure/Repository/UserCases/UpdateMyModuleUseCaseImpl.stub",
            "infrastructure/Routes/MyModuleRoutes.stub",
        ]);

        foreach ($files as $relativePath) {
            $sourceStub = $stubPath . '/' . $relativePath;
            $targetPath = $basePath . '/' . str_replace(
                    ['MyModule.stub', 'my-module.stub'],
                    [$studlyName . '.stub', $kebabName . '.php'],
                    $relativePath
                );
            $targetPath = str_replace('.stub', '.php', $targetPath);

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
}
