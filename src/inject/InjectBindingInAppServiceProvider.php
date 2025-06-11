<?php

namespace Lauchoit\LaravelHexMod\inject;

use Illuminate\Console\Command;
class InjectBindingInAppServiceProvider
{
    public static function run(Command $command, string $studlyName): void
    {
        $providerPath = base_path('app/Providers/AppServiceProvider.php');

        if (!file_exists($providerPath)) {
            $command->warn('⚠️ No se encontró AppServiceProvider.');
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
                return rtrim($matches[0]) . "\n        {$bindLine}\n        ";
            }, $file);
        } else {
            $command->info("🔁 El binding ya existe.");
        }

        file_put_contents($providerPath, $file);
        $command->info("✅ [AppServiceProvider] actualizado, Binding y use agregado para {$studlyName}.");
    }
}