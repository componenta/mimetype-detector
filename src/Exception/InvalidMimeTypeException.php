<?php

declare(strict_types=1);

namespace Componenta\Detector\Exception;

class InvalidMimeTypeException extends \InvalidArgumentException
{
    public static function forValue(string $value): self
    {
        // Do not reflect untrusted control characters or arbitrarily long input in logs.
        return new self('Invalid concrete MIME type or media type parameters.');
    }
}
