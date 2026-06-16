<?php

declare(strict_types=1);

namespace Componenta\Detector;

class FileNotFoundException extends DetectorException
{
    public static function forPath(string $path): self
    {
        return new self("File not found: $path");
    }
}
