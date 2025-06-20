<?php

namespace Lauchoit\LaravelHexMod\updates;

use Illuminate\Console\Command;

class UpdatePhpunit
{
public static function run(Command $command): void
{
$path = base_path('phpunit.xml');

if (!file_exists($path)) {
$command->warn('❌ No se encontró el archivo phpunit.xml');
return;
}

$content = file_get_contents($path);

// Verificamos si ya existe el <directory>src</directory> dentro de Feature
if (preg_match('/<testsuite name="Feature">(.+?)<\/testsuite>/s', $content, $matches)) {
    if (str_contains($matches[1], '<directory>src</directory>')) {
    $command->info("⚠️ El bloque <directory>src</directory> ya existe dentro del testsuite Feature.");
    return;
    }

    // Insertar <directory>src</directory> antes de </testsuite>
$updatedSuite = str_replace(
'</testsuite>',
"    <directory>src</directory>\n    </testsuite>",
$matches[0]
);

// Reemplazar en todo el contenido
$content = str_replace($matches[0], $updatedSuite, $content);
file_put_contents($path, $content);

$command->info('✅ Se agregó <directory>src</directory> dentro del testsuite Feature.');
return;
}

$command->warn('⚠️ No se encontró el bloque <testsuite name="Feature"> en phpunit.xml');
    }
    }
