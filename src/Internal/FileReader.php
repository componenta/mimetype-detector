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
            [$resolved, $device, $inode] = self::resolve($path);
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

                if ($stat['dev'] !== $device || $stat['ino'] !== $inode) {
                    throw new FileReadException('File changed during opening.');
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

    /**
     * @return array{string, int, int} Resolved path, device and inode.
     * @throws FileExceptionInterface
     */
    private static function resolve(string $path): array
    {
        // stat() checks the requested path rather than a cached canonical path.
        clearstatcache(true, $path);
        try {
            $stat = stat($path);
            if ($stat === false) {
                throw new FileUnavailableException('File does not exist or cannot be accessed.');
            }
        } catch (\ErrorException|\ValueError $error) {
            throw new FileUnavailableException('File does not exist or cannot be accessed.', previous: $error);
        }

        if (($stat['mode'] & 0170000) !== 0100000) {
            throw new InvalidFileException('Source is not a regular file.');
        }

        $device = $stat['dev'];
        $inode = $stat['ino'];
        if ($inode === 0) {
            // Some filesystems do not provide useful inode identifiers.
            clearstatcache(true);
        }

        try {
            $resolved = self::resolveMatchingPath($path, $device, $inode);
        } catch (FileUnavailableException) {
            // An ancestor symlink can remain cached after clearing the exact path.
            clearstatcache(true);
            $resolved = self::resolveMatchingPath($path, $device, $inode);
        }

        return [$resolved, $device, $inode];
    }

    /** @throws FileUnavailableException */
    private static function resolveMatchingPath(string $path, int $device, int $inode): string
    {
        try {
            $resolved = realpath($path);
            if ($resolved === false) {
                throw new FileUnavailableException('File does not exist or cannot be accessed.');
            }
            if ($resolved !== $path) {
                $stat = stat($resolved);
                if ($stat === false || ($stat['mode'] & 0170000) !== 0100000
                    || $stat['dev'] !== $device || $stat['ino'] !== $inode) {
                    throw new FileUnavailableException('File changed during path resolution.');
                }
            }
        } catch (\ErrorException|\ValueError $error) {
            throw new FileUnavailableException('File does not exist or cannot be accessed.', previous: $error);
        }

        return $resolved;
    }
}
