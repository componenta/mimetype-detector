<?php

declare(strict_types=1);

namespace Componenta\Detector;

class ConfigProvider extends \Componenta\Config\ConfigProvider
{
    protected function getInvokables(): array
    {
        return [
            FinfoDetector::class,
            MimeMap::class,
        ];
    }

    protected function getAliases(): array
    {
        return [
            // Main interface
            DetectorInterface::class => FinfoDetector::class,
            
            // MIME type detection
            MimeTypeDetectorInterface::class => FinfoDetector::class,
            FileMimeTypeDetectorInterface::class => FinfoDetector::class,
            
            // Extension detection
            ExtensionDetectorInterface::class => FinfoDetector::class,
            FileExtensionDetectorInterface::class => FinfoDetector::class,
            
            // MIME map
            MimeMapInterface::class => MimeMap::class,
        ];
    }
}
