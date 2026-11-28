<?php

declare(strict_types=1);

namespace Componenta\Detector\Internal {
    function fopen(string $path, string $mode): mixed
    {
        if ($path === \realpath($_SERVER['argv'][1])) {
            if (!\rename($_SERVER['argv'][2], $path)) {
                throw new \LogicException('Fixture could not substitute the regular file.');
            }
        }

        return \fopen($path, $mode);
    }

    function fread($handle, int $length): string|false
    {
        throw new \LogicException('A substituted file must not be read.');
    }
}

namespace {
    require dirname(__DIR__, 2) . '/vendor/autoload.php';

    try {
        (new \Componenta\Detector\FinfoDetector())->detectFileMimeType($_SERVER['argv'][1]);
        throw new \LogicException('Expected a file acquisition failure.');
    } catch (\Componenta\Detector\Exception\FileReadException) {
        if (file_exists($_SERVER['argv'][2])) {
            throw new \LogicException('The substitution did not occur.');
        }
        echo "Rejected substituted regular file.\n";
    }
}
