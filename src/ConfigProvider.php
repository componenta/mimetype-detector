<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Factory\FinfoDetectorFactory;

class ConfigProvider extends \Componenta\Config\ConfigProvider
{
    protected function getInvokables(): array
    {
        return [
            MimeMap::class,
        ];
    }

    protected function getFactories(): array
    {
        return [
            FinfoDetector::class => [FinfoDetectorFactory::class, 'create'],
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
            ExtensionResolverInterface::class => MimeMapInterface::class,
        ];
    }
}
