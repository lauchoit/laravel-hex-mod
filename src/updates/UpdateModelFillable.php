<?php

namespace Lauchoit\LaravelHexMod\updates;

use Illuminate\Console\Command;

class UpdateModelFillable
{
    public static function run(Command $command, string $studlyName, array $fields): void
    {
        $modelPath = app_path("Models/{$studlyName}.php");
        if (!file_exists($modelPath)) {
            $modelPath = app_path("{$studlyName}.php");
        }

        if (!file_exists($modelPath)) {
            $command->error("❌ No se encontró el modelo {$studlyName}.");
            return;
        }
        // Tipos válidos que queremos casteables
        $castTypes = ['date', 'datetime', 'boolean', 'hashed'];
        $dynamicCasts = collect($fields)
            ->map(function ($f) use ($castTypes) {
                [$key, $type] = array_pad(explode(':', $f), 2, null);
                return in_array($type, $castTypes) ? "            '{$key}' => '{$type}'," : null;
            })
            ->filter();

        // Casts fijos
        $fixedCasts = collect([
            "            'created_at' => 'datetime',",
            "            'updated_at' => 'datetime',",
        ]);

        // Merge fijo + dinámico
        $castsArray = $fixedCasts->merge($dynamicCasts)->implode("\n");



        $fieldSource = "{$studlyName}Source::FIELDS";

        $primaryKeyBlock = <<<EOT

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected \$primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected \$keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public \$incrementing = true;
EOT;

        $fillableBlock = <<<EOT

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected \$fillable = {$fieldSource};
EOT;

        $postFillableBlock = <<<EOT

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected \$hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
{$castsArray}
        ];
    }
EOT;

        $file = file_get_contents($modelPath);

        $useSourceLine = "use Lauchoit\\LaravelHexMod\\{$studlyName}\\Domain\\Entity\\{$studlyName}Source;";
        if (!str_contains($file, "{$studlyName}Source")) {
            $file = preg_replace_callback(
                '/(namespace\s+[^;]+;\s*)/',
                function ($matches) use ($useSourceLine) {
                    return $matches[1] . "\n" . $useSourceLine . "\n";
                },
                $file
            );
        }

        $file = preg_replace_callback(
            '/(use\s+HasFactory[^;]*;\s*)/',
            function ($matches) use ($primaryKeyBlock, $fillableBlock, $postFillableBlock) {
                return $matches[1]
                    . $primaryKeyBlock . "\n"
                    . $fillableBlock . "\n"
                    . $postFillableBlock . "\n";
            },
            $file
        );

        file_put_contents($modelPath, $file);
        $command->info("✅ Modelo {$studlyName} actualizado con fillable, primary key, hidden y casts (incluyendo created_at y updated_at).");
    }




}