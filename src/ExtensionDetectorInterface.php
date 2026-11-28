<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Exception\DetectorExceptionInterface;
use Componenta\Detector\Exception\StreamExceptionInterface;
use Componenta\Detector\Exception\StreamPositionRestoreExceptionInterface;
use Psr\Http\Message\StreamInterface;

interface ExtensionDetectorInterface
{
    /**
     * Resolve a preferred extension for the detected content; null means no result.
     * Strings contain raw bytes, not paths. A seekable stream is inspected from
     * its beginning and its original position is restored on success or failure.
     * A non-seekable stream consumes the inspected prefix from its current position.
     * The caller retains ownership of the stream; implementations must not close it.
     * Stream failures only apply to stream input.
     *
     * @throws DetectorExceptionInterface
     * @throws StreamExceptionInterface When reading or positioning a stream fails.
     *     Restoration failures implement StreamPositionRestoreExceptionInterface.
     */
    public function detectExtension(string|StreamInterface $content): ?Ext;
}
