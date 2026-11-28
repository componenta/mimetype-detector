<?php

declare(strict_types=1);

namespace Componenta\Detector\Internal;

use Componenta\Detector\Exception\FileExceptionInterface;
use Componenta\Detector\Exception\FileReadException;
use Componenta\Detector\Exception\FileUnavailableException;
use Componenta\Detector\Exception\InvalidFileException;

/** @internal */
final class FileReader
{
    private const int READ_CHUNK_BYTES = 65536;

    /** @throws FileExceptionInterface */
    public static function readPrefix(string $path, int $maxBytes): string
    {
        if ($path === '' || str_contains($path, "\0") || str_contains($path, '://')
            || strncasecmp($path, 'data:', 5) === 0) {
            throw new InvalidFileException('Expected a local file path.');
        }

        set_error_handler(static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }, E_WARNING | E_NOTICE);

        try {
            $resolved = self::resolve($path);
            $handle = fopen($resolved, PHP_OS_FAMILY === 'Windows' ? 'rb' : 'rbn');
            if ($handle === false) {
                throw new FileReadException('Unable to open file.');
            }

            $failure = null;
            try {
                $stat = fstat($handle);
                if ($stat === false) {
                    throw new FileReadException('Unable to inspect opened file.');
                }
                if (($stat['mode'] & 0170000) !== 0100000) {
                    throw new InvalidFileException('Source is not a regular file.');
                }

                $content = '';
                $remaining = $maxBytes;
                $madeProgress = true;
                while ($remaining > 0 && !feof($handle)) {
                    if (!$madeProgress) {
                        throw new FileReadException('File made no progress before EOF.');
                    }
                    $chunk = fread($handle, min(self::READ_CHUNK_BYTES, $remaining));
                    if ($chunk === false) {
                        throw new FileReadException('Unable to read file content.');
                    }
                    $bytes = strlen($chunk);
                    $madeProgress = $bytes !== 0;
                    $content .= $chunk;
                    $remaining -= $bytes;
                }

                return $content;
            } catch (\Throwable $error) {
                $failure = $error;
                throw $error;
            } finally {
                try {
                    $closed = fclose($handle);
                } catch (\Throwable $error) {
                    if ($failure !== null || $error instanceof \ErrorException || $error instanceof \ValueError) {
                        throw new FileReadException('Unable to close file.', previous: $error, readFailure: $failure);
                    }
                    throw $error;
                }
                if (!$closed) {
                    throw new FileReadException('Unable to close file.', readFailure: $failure);
                }
            }
        } catch (\ErrorException|\ValueError $error) {
            throw new FileReadException('Unable to read file content.', previous: $error);
        } finally {
            restore_error_handler();
        }
    }

    /** @throws FileExceptionInterface */
    private static function resolve(string $path): string
    {
        try {
            clearstatcache(true, $path);
            $resolved = realpath($path);
            if ($resolved === false) {
                throw new FileUnavailableException('File does not exist or cannot be accessed.');
            }
            $stat = stat($resolved);
            if ($stat === false) {
                throw new FileUnavailableException('File does not exist or cannot be accessed.');
            }
        } catch (\ErrorException|\ValueError $error) {
            throw new FileUnavailableException('File does not exist or cannot be accessed.', previous: $error);
        }

        if (($stat['mode'] & 0170000) !== 0100000) {
            throw new InvalidFileException('Source is not a regular file.');
        }

        return $resolved;
    }
}
