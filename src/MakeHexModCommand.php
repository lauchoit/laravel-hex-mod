<?php

namespace Lauchoit\LaravelHexMod;

use Illuminate\Console\Command;
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
        $this->updateModelFillable($studlyName, $fields);
        $this->updateMigrationFields($studlyName, $fields);

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
            "Infrastructure/Requests/CreateMyModuleRequest.stub",
            "Infrastructure/Resources/MyModuleResource.stub",
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

            if (str_contains($relativePath, 'Requests/Create')) {
                $content = $this->generateRequest($fields, $content);
            }

            if (str_contains($relativePath, 'Mappers')) {
                $content = $this->generateMapper($fields, $kebabName, $content);
            }

            if (str_contains($relativePath, 'Entity')) {
                $content = $this->generateEntity($fields, $content);
            }

            if (str_contains($relativePath, 'Resources')) {
                $content = $this->generateResource($fields, $content);
            }


            file_put_contents($targetPath, $content);
            $this->line("✅ Archivo creado: " . str_replace(base_path() . '/', '', $targetPath));
        }

        $this->generateApiResponse();
        $this->generateValidationResponse();

        sleep(1);
        Artisan::call('optimize');
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

    private function updateModelFillable(string $studlyName, array $fields): void
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
        $this->info("✅ Fillable actualizado en el modelo {$studlyName}.");
    }

    private function updateMigrationFields(string $studlyName, array $fields): void
    {
        $table = Str::snake(Str::pluralStudly($studlyName));

        $migrationFile = collect(File::files(database_path('migrations')))
            ->filter(fn($file) => str_contains($file->getFilename(), "create_{$table}_table"))
            ->first();

        if (!$migrationFile) {
            $this->warn("⚠️ No se encontró la migración para {$table}");
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
        $this->info("✅ Migración actualizada con campos: {$table}");
    }

    private function mapPhpType(string $type): string
    {
        return match ($type) {
            'string', 'text' => 'string',
            'integer', 'int' => 'int',
            'decimal', 'float', 'double' => 'float',
            'boolean', 'bool' => 'bool',
            default => 'mixed',
        };
    }

    /**
     * @param array $fields
     * @param array|bool|string $content
     * @return array|bool|string|string[]
     */
    private function generateRequest(array $fields, array|bool|string $content): string|array|bool
    {
        $rules = collect($fields)->map(function ($field) {
            $name = explode(':', $field)[0];
            return "            '{$name}' => 'required',";
        })->implode("\n");

        $rulesWrapped = "return [\n" . $rules . "\n        ];";

        $content = str_replace('{{validationRules}}', $rulesWrapped, $content);
        return $content;
    }

    /**
     * @param array $fields
     * @param $kebabName
     * @param array|bool|string $content
     * @return array|bool|string|string[]
     */
    private function generateMapper(array $fields, $kebabName, array|bool|string $content): string|array|bool
    {
        $mapperFields = collect($fields)->map(function ($field) use ($kebabName) {
            $name = explode(':', $field)[0];
            return "                {$name}: \${$kebabName}->{$name},";
        })->prepend("id: \${$kebabName}->id,")
            ->implode("\n");

        $content = str_replace('{{mapperFields}}', $mapperFields, $content);
        return $content;
    }

    /**
     * @param array $fields
     * @param array|bool|string $content
     * @return array|bool|string|string[]
     */
    private function generateEntity(array $fields, array|bool|string $content): string|array|bool
    {
        $properties = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            return "    private {$this->mapPhpType($type)} \${$name};";
        })->prepend("    private int \$id;")->implode("\n");

        $constructorParams = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            return $this->mapPhpType($type) . " \${$name}";
        })->prepend("int \$id")->implode(', ');

        $constructorBody = collect($fields)->map(function ($field) {
            $name = explode(':', $field)[0];
            return "        \$this->{$name} = \${$name};";
        })->prepend("        \$this->id = \$id;")->implode("\n");

        $gettersSetters = collect($fields)->map(function ($field) {
            [$name, $type] = explode(':', $field);
            $ucName = Str::studly($name);
            $phpType = $this->mapPhpType($type);

            return <<<EOT

                        public function get{$ucName}(): {$phpType}
                        {
                            return \$this->{$name};
                        }
                    
                        public function set{$ucName}({$phpType} \${$name}): void
                        {
                            \$this->{$name} = \${$name};
                        }
                    EOT;
        })->prepend(<<<EOT
                    
                        public function getId(): int
                        {
                            return \$this->id;
                        }
                    EOT
        )->implode("\n");

        $content = str_replace(
            ['{{properties}}', '{{constructorParams}}', '{{constructorBody}}', '{{gettersSetters}}'],
            [$properties, $constructorParams, $constructorBody, $gettersSetters],
            $content
        );
        return $content;
    }

    /**
     * @param array $fields
     * @param array|bool|string $content
     * @return array|bool|string|string[]
     */
    private function generateResource(array $fields, array|bool|string $content): string|array|bool
    {
        $resourceFields = collect($fields)->map(function ($field) {
            $name = explode(':', $field)[0];
            $method = 'get' . Str::studly($name);
            return "            '{$name}' => \$this->{$method}(),";
        })->prepend("'id' => \$this->getId(),")
            ->implode("\n");

        $content = str_replace('{{resourceFields}}', $resourceFields, $content);
        return $content;
    }

    /**
     * @return void
     */
    private function generateApiResponse(): void
    {
        $basePath = base_path("src/Shared/Responses/ApiResponse.stub");
        $stubPath = __DIR__ . '/stubs/shared/Responses/ApiResponse.stub';
        $targetPath = str_replace('.stub', '.php', $basePath);
        if (!file_exists($stubPath)) {
            $this->warn("❌ Stub no encontrado: $stubPath");
        } else {
            $dir = dirname($targetPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                }
            }
            $content = file_get_contents($stubPath);
            file_put_contents($targetPath, $content);
            $this->line("✅ Archivo creado: " . str_replace(base_path() . '/', '', $targetPath));
        }
    }

    /**
     * @return void
     */
    private function generateValidationResponse(): void
    {
        $basePath = base_path("src/Shared/Responses/ValidationResponse.stub");
        $stubPath = __DIR__ . '/stubs/shared/Responses/ValidationResponse.stub';
        $targetPath = str_replace('.stub', '.php', $basePath);
        if (!file_exists($stubPath)) {
            $this->warn("❌ Stub no encontrado: $stubPath");
        } else {
            $dir = dirname($targetPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
                }
            }
            $content = file_get_contents($stubPath);
            file_put_contents($targetPath, $content);
            $this->line("✅ Archivo creado: " . str_replace(base_path() . '/', '', $targetPath));
        }
    }

}
