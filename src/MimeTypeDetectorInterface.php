<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Psr\Http\Message\StreamInterface;

interface MimeTypeDetectorInterface
{
    /**
     * Detect a concrete MIME type; null means no result.
     * Strings contain raw bytes; seekable streams preserve their position. Non-seekable streams are consumed.
     *
     * @throws DetectorException
     */
    public function detectMimeType(string|StreamInterface $content): ?MimeType;
}
