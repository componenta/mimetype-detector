<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Psr\Http\Message\StreamInterface;

interface MimeTypeDetectorInterface
{
    /**
     * Detect MIME type from raw content
     * 
     * @param string|StreamInterface $content Raw file content
     * @param bool $asObject If true, returns MimeType object instead of string
     * @return ($asObject is true ? MimeType|null : string|null)
     */
    public function detectMimeType(string|StreamInterface $content, bool $asObject = false): string|MimeType|null;
}
