<?php

declare(strict_types=1);

use Componenta\Detector\Exception\DetectorConfigurationException;
use Componenta\Detector\FinfoDetector;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$missing = sys_get_temp_dir() . '/missing-magic-' . bin2hex(random_bytes(16));
if (!putenv('MAGIC=' . $missing)) {
    throw new RuntimeException('Cannot configure the missing magic database.');
}

// Prove the environment really makes native initialization fail.
$nativeFailed = false;
set_error_handler(static fn (int $severity, string $message): bool => true, E_WARNING | E_NOTICE);
try {
    new finfo(FILEINFO_MIME_TYPE);
} catch (Exception|ValueError) {
    $nativeFailed = true;
} finally {
    restore_error_handler();
}
if (!$nativeFailed) {
    throw new LogicException('The native failure precondition was not established.');
}

$handler = static function (): never {
    throw new LogicException('Application handler must not receive native Fileinfo warnings.');
};
set_error_handler($handler);
try {
    try {
        new FinfoDetector();
        throw new LogicException('Expected detector configuration failure.');
    } catch (DetectorConfigurationException $error) {
        if ($error->getPrevious() === null) {
            throw new LogicException('The native cause must be retained.', previous: $error);
        }
    }

    $previous = set_error_handler(static fn (): bool => false);
    restore_error_handler();
    if ($previous !== $handler) {
        throw new LogicException('Application handler was not restored.');
    }

    putenv('MAGIC');
    if ((new FinfoDetector())->detectMimeType('plain text')?->value !== 'text/plain') {
        throw new LogicException('Normal detection must still work.');
    }
    $previous = set_error_handler(static fn (): bool => false);
    restore_error_handler();
    if ($previous !== $handler) {
        throw new LogicException('Application handler was not restored after successful detection.');
    }

    echo "Configuration failure preserved; handler restored.\n";
} finally {
    restore_error_handler();
}
