<?php

declare(strict_types=1);

namespace Componenta\Detector;

interface FileMimeTypeDetectorInterface
{
    /**
     * Detect MIME type from file
     * 
     * @param string $filename Path to the file
     * @param bool $asObject If true, returns MimeType object instead of string
     * @return ($asObject is true ? MimeType|null : string|null)
     * 
     * @throws FileNotFoundException if file does not exist
     * @throws FileNotReadableException if file cannot be read
     * @throws DetectorException on other detection errors
     */
    public function detectFileMimeType(string $filename, bool $asObject = false): string|MimeType|null;
}
