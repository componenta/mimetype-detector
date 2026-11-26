<?php

declare(strict_types=1);

namespace Componenta\Detector;

interface FileMimeTypeDetectorInterface
{
    /**
     * Detect a concrete MIME type; null means no result.
     * The path must refer to a readable regular file.
     *
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws DetectorException
     */
    public function detectFileMimeType(string $filename): ?MimeType;
}
