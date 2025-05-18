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

        $studlyName = Str::studly($name);
        $kebabName  = Str::kebab($name);

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
            "Infrastructure/Model/MyModuleModel.stub",
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
}
