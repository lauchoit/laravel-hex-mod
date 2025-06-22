<?php

namespace Lauchoit\LaravelHexMod;

use Illuminate\Console\Command;
use Lauchoit\LaravelHexMod\generate\GenerateApiResponse;
use Lauchoit\LaravelHexMod\generate\GenerateEntity;
use Lauchoit\LaravelHexMod\generate\GenerateEntitySource;
use Lauchoit\LaravelHexMod\generate\GenerateMapper;
use Lauchoit\LaravelHexMod\generate\GenerateCreateRequest;
use Lauchoit\LaravelHexMod\generate\GenerateModel;
use Lauchoit\LaravelHexMod\generate\GenerateResource;
use Lauchoit\LaravelHexMod\generate\GenerateUpdateRequest;
use Lauchoit\LaravelHexMod\generate\GenerateValidationResponse;
use Lauchoit\LaravelHexMod\generate\Shared\ValidFieldType;
use Lauchoit\LaravelHexMod\generate\Test\Feature\GenerateTestFeatureCreate;
use Lauchoit\LaravelHexMod\generate\Test\Feature\GenerateTestFeatureFindAll;
use Lauchoit\LaravelHexMod\generate\Test\Feature\GenerateTestFeatureFindById;
use Lauchoit\LaravelHexMod\generate\Test\Feature\GenerateTestFeatureUpdateById;
use Lauchoit\LaravelHexMod\generate\Test\Unit\GenerateTestEntity;
use Lauchoit\LaravelHexMod\generate\Test\Unit\GenerateTestException;
use Lauchoit\LaravelHexMod\inject\InjectBindingInAppServiceProvider;
use Lauchoit\LaravelHexMod\inject\InjectModulesRoutes;
use Lauchoit\LaravelHexMod\updates\UpdateFactory;
use Lauchoit\LaravelHexMod\updates\UpdateMigrationFields;
use Lauchoit\LaravelHexMod\updates\UpdatePhpunit;
use Illuminate\Support\{Str, Arr};
use Illuminate\Support\Facades\{Artisan, File};

class MakeHexModCommand extends Command
{
    protected $signature = 'make:hex-mod {name} {--f|field=*}';
    protected $description = 'Create a hexagonal architecture base structure for a module';

    public function handle(): void
    {
        $name = $this->argument('name');

        $existsSrc = base_path('src');
        if (is_dir($existsSrc)) {
            $modules = collect(scandir(base_path('src')))
                ->filter(fn($item) => !in_array($item, ['.', '..']) && is_dir(base_path("src/{$item}")))
                ->values()
                ->all();

            if (in_array(strtolower($name), array_map('strtolower', $modules))) {
                $this->warn("⚠️ This module {$name} already exists.");
                return;
            }
        }

        // Procesar los campos
        $inputFields = $this->option('field');
        $rawFields = collect($inputFields)
            ->flatMap(fn($f) => explode(',', $f))
            ->filter()
            ->values();

        if ($rawFields->isEmpty()) {
            $fields = ['attribute1:string', 'attribute2:string'];
        } else {
            $fields = $rawFields->map(function ($f) {
                if (!str_contains($f, ':')) {
                    return "{$f}:string";
                }
                return $f;
            })->toArray();
        }

        foreach ($fields as $field) {
            [$_, $type] = explode(':', $field);
            if (!ValidFieldType::isValid($type)) {
                $this->error(ValidFieldType::errorMessage($type));
                return;
            }
        }

        $studlyName = Str::studly($name);
        $kebabName  = Str::kebab($name);
        $camelName  = Str::camel($name);
        $snakeName = Str::snake(Str::pluralStudly($studlyName));

        $tableName = "create_" . Str::snake(Str::pluralStudly($studlyName)) . "_table";
        Artisan::call("make:migration $tableName");
        Artisan::call("make:factory {$studlyName}Factory");





        // Updates y generación
        UpdatePhpunit::run($this);
        UpdateMigrationFields::run($this, $studlyName, $fields);
        UpdateFactory::run($this, $studlyName, $fields);
        InjectBindingInAppServiceProvider::run($this, $studlyName);
        InjectModulesRoutes::run($this, $studlyName, $kebabName);

        $basePath = base_path("src/{$studlyName}");
        $stubPath = __DIR__ . '/stubs';

        $this->info("Generando módulo {$studlyName} en: {$basePath}");

        $files = collect([
            "Domain/Entity/MyModule.stub",
            "Domain/Entity/MyModuleSource.stub",
            "Domain/Mappers/MyModuleMapper.stub",
            "Domain/Repository/MyModuleRepository.stub",
            "Domain/Exceptions/MyModuleNotFoundException.stub",
            "Application/UseCases/CreateMyModuleUseCase.stub",
            "Application/UseCases/DeleteByIdMyModuleUseCase.stub",
            "Application/UseCases/FindAllMyModuleUseCase.stub",
            "Application/UseCases/FindByIdMyModuleUseCase.stub",
            "Application/UseCases/UpdateByIdMyModuleUseCase.stub",
            "Infrastructure/Model/MyModule.stub",
            "Infrastructure/Requests/CreateMyModuleRequest.stub",
            "Infrastructure/Requests/UpdateMyModuleRequest.stub",
            "Infrastructure/Controllers/MyModuleController.stub",
            "Infrastructure/Repository/MyModuleRepositoryImpl.stub",
            "Infrastructure/Repository/UseCases/CreateMyModuleUseCaseImpl.stub",
            "Infrastructure/Repository/UseCases/DeleteByIdMyModuleUseCaseImpl.stub",
            "Infrastructure/Repository/UseCases/FindAllMyModuleUseCaseImpl.stub",
            "Infrastructure/Repository/UseCases/FindByIdMyModuleUseCaseImpl.stub",
            "Infrastructure/Repository/UseCases/UpdateByIdMyModuleUseCaseImpl.stub",
            "Infrastructure/Resources/MyModuleResource.stub",
            "Infrastructure/Routes/MyModuleRoutes.stub",
            "Tests/Unit/Domain/Entity/MyModuleTest.stub",
            "Tests/Unit/Domain/Entity/MyModuleSourceTest.stub",
            "Tests/Unit/Domain/Exceptions/MyModuleNotFoundExceptionTest.stub",
            "Tests/Feature/CreateMyModuleTest.stub",
            "Tests/Feature/DeleteByIdMyModuleTest.stub",
            "Tests/Feature/FindAllMyModuleTest.stub",
            "Tests/Feature/FindByIdMyModuleTest.stub",
            "Tests/Feature/UpdateByIdMyModuleTest.stub",
        ]);

        foreach ($files as $relativePath) {
            $sourceStub = $stubPath . '/' . $relativePath;
            $relativePath = str_replace(
                ['MyModule', 'my-module'],
                [$studlyName, $kebabName],
                $relativePath
            );

            $targetPath = $basePath . '/' . str_replace('.stub', '.php', $relativePath);
            $dir = dirname($targetPath);

            if (!file_exists($sourceStub)) {
                $this->warn("❌ Stub no encontrado: $sourceStub");
                continue;
            }

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $content = file_get_contents($sourceStub);
            $content = str_replace(
                ['{{StudlyName}}', '{{kebabName}}', '{{camelName}}', '{{snakeName}}'],
                [$studlyName, $kebabName, $camelName, $snakeName],
                $content
            );

            if (str_contains($relativePath, 'Requests/Create')) {
                $content = GenerateCreateRequest::run($fields, $content);
            }

            if (str_contains($relativePath, 'Requests/Update')) {
                $content = GenerateUpdateRequest::run($fields, $content);
            }

            if (str_contains($relativePath, 'Mappers')) {
                $content = GenerateMapper::run($fields, $camelName, $content);
            }

            if (str_contains($relativePath, "Entity/{$studlyName}.stub")) {
                $content = GenerateEntity::run($fields, $content);
            }

            if (str_contains($relativePath, "Entity/{$studlyName}Source.stub")) {
                $content = GenerateEntitySource::run($fields, $content);
            }

            if (str_contains($relativePath, 'Resources')) {
                $content = GenerateResource::run($fields, $content, $camelName);
            }

            if (str_contains($relativePath, "Tests/Unit/Domain/Entity/{$studlyName}Test.stub")) {
                $content = GenerateTestEntity::run($fields, $content, $camelName);
            }

            if (str_contains($relativePath, "Tests/Unit/Domain/Entity/{$studlyName}SourceTest.stub")) {
                $content = GenerateTestEntity::runSource($fields, $content, $camelName);
            }

            if (str_contains($relativePath, "Tests/Unit/Domain/Exceptions/{$studlyName}NotFoundExceptionTest.stub")) {
                $content = GenerateTestException::run($studlyName, $content);
            }

            if (str_contains($relativePath, "Infrastructure/Model/{$studlyName}.stub")) {
                $content = GenerateModel::run($studlyName, $fields, $content);
            }

            if (str_contains($relativePath, "Tests/Feature/Create{$studlyName}Test.stub")) {
                $content = GenerateTestFeatureCreate::run($fields, $content, $camelName, $kebabName, $studlyName, $snakeName);
            }

            if (str_contains($relativePath, "Tests/Feature/FindAll{$studlyName}Test.stub")) {
                $content = GenerateTestFeatureFindAll::run($fields, $content, $camelName, $studlyName);
            }

            if (str_contains($relativePath, "Tests/Feature/FindById{$studlyName}Test.stub")) {
                $content = GenerateTestFeatureFindById::run($fields, $content, $camelName, $studlyName);
            }

            if (str_contains($relativePath, "Tests/Feature/UpdateById{$studlyName}Test.stub")) {
                $content = GenerateTestFeatureUpdateById::run($fields, $content, $camelName, $studlyName, $snakeName);
            }

            file_put_contents($targetPath, $content);
            $this->line("✅ Archivo creado: " . str_replace(base_path() . '/', '', $targetPath));
        }

        GenerateApiResponse::run($this);
        GenerateValidationResponse::run($this);

        Artisan::call('optimize:clear');
        $this->line(Artisan::output());
        $this->info("🎉 Módulo {$studlyName} generado correctamente.");
    }
}
