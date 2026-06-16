<?php

declare(strict_types=1);

namespace Componenta\Detector;

interface FileExtensionDetectorInterface
{
    /**
     * Detect file extension from file
     * 
     * @param string $filename Path to the file
     * @param bool $asObject If true, returns Ext object instead of string
     * @return ($asObject is true ? Ext|null : string|null)
     * 
     * @throws FileNotFoundException if file does not exist
     * @throws FileNotReadableException if file cannot be read
     * @throws DetectorException on other detection errors
     */
    public function detectFileExtension(string $filename, bool $asObject = false): string|Ext|null;
}
