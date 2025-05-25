<?php

namespace Lauchoit\LaravelHexMod;

use Illuminate\Console\Command;
use Lauchoit\LaravelHexMod\generate\GenerateApiResponse;
use Lauchoit\LaravelHexMod\generate\GenerateEntity;
use Lauchoit\LaravelHexMod\generate\GenerateMapper;
use Lauchoit\LaravelHexMod\generate\GenerateRequest;
use Lauchoit\LaravelHexMod\generate\GenerateResource;
use Lauchoit\LaravelHexMod\generate\GenerateValidationResponse;
use Lauchoit\LaravelHexMod\inject\InjectBindingInAppServiceProvider;
use Lauchoit\LaravelHexMod\inject\InjectModulesRoutes;
use Lauchoit\LaravelHexMod\updates\UpdateMigrationFields;
use Lauchoit\LaravelHexMod\updates\UpdateModelFillable;
use Illuminate\Support\{Str, Arr};
use Illuminate\Support\Facades\{Artisan, File};

class MakeHexModCommand extends Command
{
    protected $signature = 'make:hex-mod {name} {--fields=*}';
    protected $description = 'Genera la estructura hexagonal base para un módulo';

    public function handle(): void
    {
        $name = $this->argument('name');

        $existsSrc = base_path('src');
        if (is_dir($existsSrc)) {

        $modules = collect(scandir(base_path('src')))
            ->filter(fn($item) => !in_array($item, ['.', '..']) && is_dir(base_path("src/{$item}")))
            ->values()
            ->all();

        $exists = in_array(strtolower($name), array_map('strtolower', $modules));
        if($exists) {
            $this->warn("⚠️ This module {$name} already.");
            return;
            }
        }

        $studlyName = Str::studly($name);
        $kebabName  = Str::kebab($name);

        Artisan::call('make:model', [
            'name' => $studlyName,
            '-m' => true,
            '-f' => true,
        ]);

        $fields = $this->option('fields');
        if (empty($fields)) {
            $fields = ['attribute1:string', 'attribute2:string'];
        }
        UpdateModelFillable::run($this, $studlyName, $fields);
        UpdateMigrationFields::run($this, $studlyName, $fields);

        InjectBindingInAppServiceProvider::run($this, $studlyName);
        InjectModulesRoutes::run($this, $studlyName, $kebabName);

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
            "Infrastructure/Requests/CreateMyModuleRequest.stub",
            "Infrastructure/Resources/MyModuleResource.stub",
            "Infrastructure/Routes/MyModuleRoutes.stub",
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

            if (str_contains($relativePath, 'Requests/Create')) {
                $content = GenerateRequest::run($fields, $content);
            }

            if (str_contains($relativePath, 'Mappers')) {
                $content = GenerateMapper::run($fields, $kebabName, $content);
            }

            if (str_contains($relativePath, 'Entity')) {
                $content = GenerateEntity::run($fields, $content);
            }

            if (str_contains($relativePath, 'Resources')) {
                $content = GenerateResource::run($fields, $content);
            }


            file_put_contents($targetPath, $content);
            $this->line("✅ Archivo creado: " . str_replace(base_path() . '/', '', $targetPath));
        }

        GenerateApiResponse::run($this);

        GenerateValidationResponse::run($this);

        sleep(1);
        Artisan::call('optimize');
        $this->info("🎉 Módulo {$studlyName} generado correctamente.");
    }
}
