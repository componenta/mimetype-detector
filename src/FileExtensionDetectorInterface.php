<?php

declare(strict_types=1);

namespace Componenta\Detector;

interface FileExtensionDetectorInterface
{
    /**
     * Resolve a preferred extension for the detected content; null means no result.
     * The path must refer to a readable regular file.
     *
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws DetectorException
     */
    public function detectFileExtension(string $filename): ?Ext;
}
