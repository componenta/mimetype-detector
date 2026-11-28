<?php

declare(strict_types=1);

namespace Componenta\Stdlib {
    // This fixture runs in its own PHP process. Schedule a real filesystem
    // replacement immediately after the last path-based type check.
    function stat(string $path): array|false
    {
        $stat = \stat($path);
        if ($stat !== false && $path === \realpath($_SERVER['argv'][1])) {
            if (!\rename($_SERVER['argv'][2], $path)) {
                throw new \RuntimeException('Unable to replace the file with a FIFO.');
            }
        }

        return $stat;
    }
}

namespace {
    require dirname(__DIR__, 2) . '/vendor/autoload.php';

    \pcntl_alarm(5);
    try {
        (new \Componenta\Detector\FinfoDetector())->detectFileMimeType($_SERVER['argv'][1]);
        fwrite(STDERR, "Expected InvalidFileException.\n");
        exit(1);
    } catch (\Componenta\Detector\Exception\InvalidFileException) {
        clearstatcache(true, $_SERVER['argv'][1]);
        if (file_exists($_SERVER['argv'][2]) || filetype($_SERVER['argv'][1]) !== 'fifo') {
            fwrite(STDERR, "The replacement did not happen.\n");
            exit(3);
        }
        echo "Rejected substituted FIFO.\n";
    } finally {
        \pcntl_alarm(0);
    }
}
