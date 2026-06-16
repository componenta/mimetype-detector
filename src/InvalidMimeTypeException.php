<?php

declare(strict_types=1);

namespace Componenta\Detector;

class InvalidMimeTypeException extends \InvalidArgumentException
{
    public static function forValue(string $value): self
    {
        return new self("Invalid MIME type format: '$value'. Expected format: type/subtype");
    }
}
