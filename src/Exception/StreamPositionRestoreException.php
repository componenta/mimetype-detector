<?php

declare(strict_types=1);

namespace Componenta\Detector\Exception;

/** The original stream position could not be restored. */
final class StreamPositionRestoreException extends \RuntimeException implements StreamPositionRestoreExceptionInterface
{
    public function __construct(
        \Throwable $restorationFailure,
        public readonly ?\Throwable $readFailure = null,
    ) {
        parent::__construct(
            $readFailure === null
                ? 'Unable to restore the stream position.'
                : 'Unable to read stream content and restore its original position.',
            previous: $restorationFailure,
        );
    }
}
