<?php
namespace Lauchoit\LaravelHexMod\updates;

use Illuminate\Console\Command;

class UpdateFactory
{
    public static function run(Command $command, string $studlyName, array $fields): void
    {
        $factoryPath = base_path("database/factories/{$studlyName}Factory.php");

        if (!file_exists($factoryPath)) {
            $command->error("❌ No se encontró el factory {$studlyName}Factory.");
            return;
        }

        $qualifiedModel = "Lauchoit\\LaravelHexMod\\{$studlyName}\\Infrastructure\\Model\\{$studlyName}";
        $useLine = "use {$qualifiedModel};";
        $extendsLine = "@extends \\Illuminate\\Database\\Eloquent\\Factories\\Factory<{$studlyName}>";
        $modelBlock = <<<PHP
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<{$studlyName}>
     */
    protected \$model = {$studlyName}::class;

PHP;

        $definitionMethod = <<<PHP
    public function definition(): array
    {
        return [
            //
        ];
    }
PHP;

        $content = file_get_contents($factoryPath);

        // Asegura que la línea `use Model;` esté
        $content = preg_replace(
            '/use Illuminate\\\Database\\\Eloquent\\\Factories\\\Factory;/',
            "use Illuminate\\Database\\Eloquent\\Factories\\\Factory;\n{$useLine}",
            $content
        );

        // Reemplaza el @extends
        $content = preg_replace(
            '/@extends.*\n/',
            "{$extendsLine}\n",
            $content
        );

        // Inserta el bloque $model (reemplaza si ya existe)
        if (str_contains($content, 'protected $model')) {
            $content = preg_replace('/protected \$model = .*?;(\n)?/', "{$modelBlock}\n", $content);
        } else {
            $content = preg_replace('/class .*? extends Factory\s*\{/', "$0\n\n{$modelBlock}", $content);
        }

        // Reemplaza el método definition()
        $content = preg_replace(
            '/public function definition\(\): array\s*\{[^}]+\}/s',
            $definitionMethod,
            $content
        );

        file_put_contents($factoryPath, $content);
        $command->info("✅ Factory {$studlyName}Factory actualizado con estructura limpia.");
    }
}
