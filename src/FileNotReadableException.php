<?php

declare(strict_types=1);

namespace Componenta\Detector;

class FileNotReadableException extends DetectorException
{
    public static function forPath(string $path): self
    {
        return new self("File not readable: $path");
    }
}
