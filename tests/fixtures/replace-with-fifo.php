<?php

declare(strict_types=1);

namespace Componenta\Detector {
    // This fixture runs in its own PHP process. Schedule a real filesystem
    // replacement immediately after the last path-based readability check.
    function is_readable(string $path): bool
    {
        $readable = \is_readable($path);
        if ($readable && $path === $_SERVER['argv'][1]) {
            if (!\rename($_SERVER['argv'][2], $path)) {
                throw new \RuntimeException('Unable to replace the file with a FIFO.');
            }
        }

        return $readable;
    }
}

namespace {
    require dirname(__DIR__, 2) . '/vendor/autoload.php';

    \pcntl_alarm(5);
    try {
        (new \Componenta\Detector\FinfoDetector())->detectFileMimeType($_SERVER['argv'][1]);
        fwrite(STDERR, "Expected FileNotReadableException.\n");
        exit(1);
    } catch (\Componenta\Detector\FileNotReadableException) {
        echo "Rejected substituted FIFO.\n";
    } finally {
        \pcntl_alarm(0);
    }
}
