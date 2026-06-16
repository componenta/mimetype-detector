<?php

declare(strict_types=1);

namespace Componenta\Detector;

interface DetectorInterface extends
    MimeTypeDetectorInterface,
    FileMimeTypeDetectorInterface,
    ExtensionDetectorInterface,
    FileExtensionDetectorInterface
{
}
