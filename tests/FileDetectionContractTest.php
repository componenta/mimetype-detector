<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\FinfoDetector;
use PHPUnit\Framework\Assert;

test('a retargeted ancestor symlink resolves to current content', function (bool $removeOldFile): void {
    if (PHP_OS_FAMILY === 'Windows' || !function_exists('symlink') || !function_exists('proc_open')) {
        $this->markTestSkipped('POSIX symlinks and subprocess support required.');
    }

    $directory = sys_get_temp_dir() . '/mime-retarget-' . bin2hex(random_bytes(8));
    Assert::assertTrue(mkdir($directory, 0700));
    $directory = realpath($directory);
    Assert::assertIsString($directory);
    $first = $directory . '/first';
    $second = $directory . '/second';
    $link = $directory . '/link';
    $replacement = $directory . '/replacement';
    $path = $link . '/source';
    try {
        Assert::assertTrue(mkdir($first, 0700));
        Assert::assertTrue(mkdir($second, 0700));
        file_put_contents($first . '/source', 'plain text');
        file_put_contents($second . '/source', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n");
        Assert::assertTrue(symlink($first, $link));

        $detector = new FinfoDetector();
        Assert::assertSame('text/plain', $detector->detectFileMimeType($path)?->value);

        // A separate process cannot invalidate this process's PHP realpath cache.
        $script = <<<'PHP'
if (!symlink($argv[1], $argv[2]) || !rename($argv[2], $argv[3])) {
    exit(1);
}
if ($argv[4] === 'remove' && !unlink($argv[5])) {
    exit(2);
}
PHP;
        $process = proc_open(
            [PHP_BINARY, '-r', $script, $second, $replacement, $link, $removeOldFile ? 'remove' : 'keep', $first . '/source'],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );
        Assert::assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        Assert::assertSame(0, proc_close($process), (string) $output . (string) $errors);
        Assert::assertSame($second, readlink($link));

        Assert::assertSame('application/pdf', $detector->detectFileMimeType($path)?->value);
        Assert::assertSame('pdf', $detector->detectFileExtension($path)?->value);
    } finally {
        foreach ([$replacement, $link, $first . '/source', $second . '/source'] as $entry) {
            if (is_link($entry) || is_file($entry)) {
                unlink($entry);
            }
        }
        foreach ([$first, $second, $directory] as $entry) {
            if (is_dir($entry)) {
                rmdir($entry);
            }
        }
    }
})->with([false, true]);

test('a regular file substituted after resolution is rejected before reading', function (): void {
    if (PHP_OS_FAMILY === 'Windows' || !function_exists('proc_open')) {
        $this->markTestSkipped('POSIX file identity and subprocess support required.');
    }
    $directory = sys_get_temp_dir() . '/mime-substitution-' . bin2hex(random_bytes(8));
    Assert::assertTrue(mkdir($directory, 0700));
    $path = $directory . '/source';
    $replacement = $directory . '/replacement';
    try {
        file_put_contents($path, 'plain text');
        file_put_contents($replacement, "%PDF-1.4\n");
        $process = proc_open(
            [PHP_BINARY, __DIR__ . '/fixtures/replace-with-regular-file.php', $path, $replacement],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );
        Assert::assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        Assert::assertSame(0, proc_close($process), (string) $errors);
        Assert::assertSame("Rejected substituted regular file.\n", $output);
    } finally {
        foreach ([$path, $replacement] as $entry) {
            if (is_file($entry)) {
                unlink($entry);
            }
        }
        rmdir($directory);
    }
});
