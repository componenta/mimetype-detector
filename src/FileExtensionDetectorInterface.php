<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Exception\DetectorExceptionInterface;
use Componenta\Detector\Exception\FileExceptionInterface;

interface FileExtensionDetectorInterface
{
    /**
     * Resolve a preferred extension for the detected content; null means no result.
     * The path must refer to a caller-authorized readable regular local file. Symlinks to regular files are allowed.
     *
     * @throws FileExceptionInterface
     * @throws DetectorExceptionInterface
     */
    public function detectFileExtension(string $filename): ?Ext;
}
