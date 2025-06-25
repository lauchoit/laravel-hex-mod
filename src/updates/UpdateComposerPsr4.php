<?php

namespace Lauchoit\LaravelHexMod\updates;

class UpdateComposerPsr4
{
    public static function run(string $composerPath = 'composer.json'): void
    {
        if (!file_exists($composerPath)) {
            throw new \RuntimeException("composer.json not found at $composerPath");
        }

        $composer = json_decode(file_get_contents($composerPath), true);

        if (!isset($composer['autoload']['psr-4'])) {
            $composer['autoload']['psr-4'] = [];
        }

        $psr4 = &$composer['autoload']['psr-4'];

        if (!array_key_exists('Lauchoit\\LaravelHexMod\\', $psr4)) {
            $psr4['Lauchoit\\LaravelHexMod\\'] = 'src/';
            echo "✅ Namespace 'Lauchoit\\\\LaravelHexMod\\\\' added to PSR-4 autoload.\n";

            // Reescribir el composer.json con formato bonito
            file_put_contents($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            echo "🔄 composer.json updated. You may want to run: composer dump-autoload\n";
        } else {
            echo "✔️ Namespace 'Lauchoit\\\\LaravelHexMod\\\\' already exists in PSR-4 autoload.\n";
        }
    }
}
