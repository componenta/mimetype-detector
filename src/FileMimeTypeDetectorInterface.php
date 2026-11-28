<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Exception\DetectorExceptionInterface;
use Componenta\Detector\Exception\FileExceptionInterface;

interface FileMimeTypeDetectorInterface
{
    /**
     * Detect a concrete MIME type; null means no result.
     * The path must refer to a caller-authorized readable regular local file. Symlinks to regular files are allowed.
     *
     * @throws FileExceptionInterface
     * @throws DetectorExceptionInterface
     */
    public function detectFileMimeType(string $filename): ?MimeType;
}
