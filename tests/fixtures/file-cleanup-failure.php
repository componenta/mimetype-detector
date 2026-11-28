<?php

declare(strict_types=1);

namespace Componenta\Detector\Internal {
    function fread($handle, int $length): string|false
    {
        if (!\is_resource($handle)) {
            throw new \LogicException('Expected an opened handle.');
        }

        throw new \LogicException('Original read defect.');
    }

    function fclose($handle): bool
    {
        if (!\fclose($handle)) {
            throw new \LogicException('Fixture failed to close the real resource.');
        }

        throw new \ErrorException('Simulated close failure.');
    }
}

namespace {
    require dirname(__DIR__, 2) . '/vendor/autoload.php';

    $handler = static function (): never {
        throw new \LogicException('Application warning handler must not receive file warnings.');
    };
    set_error_handler($handler);
    try {
        try {
            (new \Componenta\Detector\FinfoDetector())->detectFileMimeType($_SERVER['argv'][1]);
            throw new \LogicException('Expected a cleanup failure.');
        } catch (\Componenta\Detector\Exception\FileReadException $error) {
            if (!$error->getPrevious() instanceof \ErrorException
                || $error->getPrevious()->getMessage() !== 'Simulated close failure.'
                || !$error->readFailure instanceof \LogicException
                || $error->readFailure->getMessage() !== 'Original read defect.') {
                throw new \LogicException('Both causes must be preserved.', previous: $error);
            }
        }

        $previous = set_error_handler(static fn(): bool => false);
        restore_error_handler();
        if ($previous !== $handler) {
            throw new \LogicException('Application error handler was not restored.');
        }

        echo "Both failures preserved; handler restored.\n";
    } finally {
        restore_error_handler();
    }
}
