<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Exception\DetectorExceptionInterface;
use Componenta\Detector\Exception\StreamExceptionInterface;
use Psr\Http\Message\StreamInterface;

interface MimeTypeDetectorInterface
{
    /**
     * Detect a concrete MIME type; null means no result.
     * Strings contain raw bytes; seekable streams preserve their position. Non-seekable streams consume the inspected prefix. Stream failures only apply to stream input.
     *
     * @throws DetectorExceptionInterface
     * @throws StreamExceptionInterface When the source is a stream.
     */
    public function detectMimeType(string|StreamInterface $content): ?MimeType;
}
