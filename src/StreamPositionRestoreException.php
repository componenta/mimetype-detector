<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Throwable;

/** The original position could not be restored; the stream may be unusable. */
final class StreamPositionRestoreException extends DetectorException
{
    public function __construct(
        Throwable $restorationFailure,
        public readonly ?DetectorException $readFailure = null,
    ) {
        parent::__construct(
            $readFailure === null
                ? 'Unable to restore the stream position.'
                : 'Unable to read stream content and restore its original position.',
            previous: $restorationFailure,
        );
    }
}
