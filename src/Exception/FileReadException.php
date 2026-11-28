<?php

declare(strict_types=1);

namespace Componenta\Detector\Exception;

/** A file I/O failure; a preceding failure is retained when closing also fails. */
class FileReadException extends \RuntimeException implements FileExceptionInterface
{
    public function __construct(
        string $message,
        int $code = 0,
        ?\Throwable $previous = null,
        public readonly ?\Throwable $readFailure = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
