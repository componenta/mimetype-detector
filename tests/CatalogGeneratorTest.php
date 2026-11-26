<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use PHPUnit\Framework\Assert;

test('catalog generation rejects invalid records without changing generated sources', function (): void {
    $root = dirname(__DIR__);
    $tmp = sys_get_temp_dir() . '/mime-catalog-' . bin2hex(random_bytes(8));
    $paths = [
        'scripts/generate-catalog.php',
        'src/InvalidMimeTypeException.php',
        'src/Internal/MediaTypeParser.php',
        'src/Ext.php',
        'src/Internal/DefaultMimeMap.php',
        ...array_map(
            static fn (string $enum): string => 'src/MimeTypes/' . $enum . '.php',
            ['Application', 'Audio', 'Font', 'Image', 'Message', 'Model', 'Multipart', 'Text', 'Video'],
        ),
    ];

    $run = static function (string $directory, bool $check = false): array {
        $command = [PHP_BINARY, $directory . '/scripts/generate-catalog.php'];
        if ($check) {
            $command[] = '--check';
        }
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        Assert::assertIsResource($process);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $stdout . $stderr];
    };

    try {
        foreach ($paths as $path) {
            $directory = dirname($tmp . '/' . $path);
            if (!is_dir($directory)) {
                mkdir($directory, 0700, true);
            }
            Assert::assertTrue(copy($root . '/' . $path, $tmp . '/' . $path));
        }
        mkdir($tmp . '/resources', 0700, true);
        $catalog = json_decode(file_get_contents($root . '/resources/mime-catalog.json'), true, flags: JSON_THROW_ON_ERROR);
        $writeCatalog = static function (array $data) use ($tmp): void {
            file_put_contents($tmp . '/resources/mime-catalog.json', json_encode($data, JSON_THROW_ON_ERROR));
        };
        $writeCatalog($catalog);
        Assert::assertSame([0, ''], $run($tmp, check: true));

        $generated = array_values(array_filter($paths, static fn (string $p): bool => str_starts_with($p, 'src/MimeTypes/') || $p === 'src/Internal/DefaultMimeMap.php'));
        $hashes = static fn (): array => array_map(static fn (string $p): string => hash_file('sha256', $tmp . '/' . $p), $generated);
        $before = $hashes();

        $invalidCases = [
            'malformed record' => ['text/x-invalid' => 'not an object'],
            'unknown property' => ['text/x-invalid' => ['extension' => ['bad']]],
            'null extension list' => ['text/x-invalid' => ['extensions' => null]],
            'invalid extension' => ['text/x-invalid' => ['extensions' => [[]]]],
            'late duplicate enum member' => ['video/x-invalid' => ['enums' => ['Video' => [['name' => 'Mp4', 'order' => 10000]]]]],
            'wrong enum category' => ['text/x-invalid' => ['enums' => ['Image' => [['name' => 'Invalid', 'order' => 10000]]]]],
        ];
        foreach ($invalidCases as $label => $entries) {
            $modified = $catalog;
            // Ensure generation would change the map if it wrote prematurely.
            $modified['types']['image/jpeg']['extensions'] = ['jpeg', 'jpg'];
            foreach ($entries as $mime => $record) {
                $modified['types'][$mime] = $record;
            }
            $writeCatalog($modified);
            [$exit, $output] = $run($tmp);
            Assert::assertNotSame(0, $exit, $label . ': ' . $output);
            Assert::assertSame($before, $hashes(), $label);
        }

        $modified = $catalog;
        $modified['types'] = ['image/x-early-alias' => ['extensionAliasOf' => 'image/jpeg']] + $modified['types'];
        $writeCatalog($modified);
        [$exit, $output] = $run($tmp);
        Assert::assertNotSame(0, $exit);
        Assert::assertStringContainsString('Extension alias must follow its canonical type', $output);
        Assert::assertSame($before, $hashes());
    } finally {
        if (is_dir($tmp)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tmp, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    rmdir($item->getPathname());
                } else {
                    unlink($item->getPathname());
                }
            }
            rmdir($tmp);
        }
    }
});
