<?php

declare(strict_types=1);

namespace Componenta\Detector;

use finfo;
use Psr\Http\Message\StreamInterface;
use Throwable;

final readonly class FinfoDetector implements DetectorInterface
{
    private const int READ_CHUNK_BYTES = 65536;

    private finfo $finfo;
    private MimeMapInterface $mimeMap;

    public function __construct(?MimeMapInterface $mimeMap = null, private int $maxBytes = 8192)
    {
        if ($maxBytes < 1) {
            throw new \InvalidArgumentException('The detection byte limit must be positive.');
        }

        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
        $this->mimeMap = $mimeMap ?? new MimeMap();
    }

    public function detectMimeType(string|StreamInterface $content): ?MimeType
    {
        return $this->detectBuffer(
            $content instanceof StreamInterface ? $this->readStream($content) : substr($content, 0, $this->maxBytes),
        );
    }

    public function detectFileMimeType(string $filename): ?MimeType
    {
        return $this->detectBuffer($this->readFile($filename));
    }

    public function detectExtension(string|StreamInterface $content): ?Ext
    {
        return $this->resolveExtension($this->detectMimeType($content));
    }

    public function detectFileExtension(string $filename): ?Ext
    {
        return $this->resolveExtension($this->detectFileMimeType($filename));
    }

    private function detectBuffer(string $buffer): ?MimeType
    {
        if ($buffer === '') {
            return null;
        }

        try {
            $value = @$this->finfo->buffer($buffer);
        } catch (Throwable $exception) {
            throw new DetectorException('Unable to classify content.', previous: $exception);
        }

        if ($value === false) {
            throw new DetectorException('Unable to classify content.');
        }
        if (in_array($value, ['???', 'application/x-empty', 'inode/x-empty'], true)) {
            return null;
        }

        return MimeType::tryCreate($value)
            ?? throw new DetectorException('The MIME detection backend returned an invalid media type.');
    }

    private function resolveExtension(?MimeType $mime): ?Ext
    {
        if ($mime === null) {
            return null;
        }

        try {
            $extension = $this->mimeMap->getExtension($mime->value);
        } catch (DetectorException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new DetectorException('Unable to resolve a mapped extension.', previous: $exception);
        }

        if ($extension === null) {
            return null;
        }

        return Ext::tryCreate($extension)
            ?? throw new DetectorException('The MIME map returned an invalid extension.');
    }

    private function readStream(StreamInterface $stream): string
    {
        $position = null;
        $content = '';
        $failure = null;

        try {
            if (!$stream->isReadable()) {
                throw new DetectorException('Stream is not readable.');
            }
            if ($stream->isSeekable()) {
                $position = $stream->tell();
                $stream->rewind();
            }

            $remaining = $this->maxBytes;
            $madeProgress = true;
            while ($remaining > 0 && !$stream->eof()) {
                // An empty read is valid only if the stream has now reached EOF.
                if (!$madeProgress) {
                    throw new DetectorException('Stream read made no progress before EOF.');
                }

                $length = min(self::READ_CHUNK_BYTES, $remaining);
                $chunk = $stream->read($length);
                $bytes = strlen($chunk);
                if ($bytes > $length) {
                    throw new DetectorException('Stream returned more bytes than requested.');
                }
                $madeProgress = $bytes !== 0;
                $content .= $chunk;
                $remaining -= $bytes;
            }
        } catch (Throwable $exception) {
            $failure = $exception instanceof DetectorException
                ? $exception
                : new DetectorException('Unable to read stream content.', previous: $exception);
        }

        if ($position !== null) {
            try {
                $stream->seek($position);
            } catch (Throwable $exception) {
                throw new StreamPositionRestoreException($exception, $failure);
            }
        }

        if ($failure !== null) {
            throw $failure;
        }

        return $content;
    }

    private function readFile(string $filename): string
    {
        // File paths are caller-authorized local paths. A detector is not a path sandbox.
        if ($filename === '' || str_contains($filename, "\0") || str_contains($filename, '://')) {
            throw new FileNotReadableException('Expected a local filesystem path.');
        }

        try {
            clearstatcache(true, $filename);
            if (!@file_exists($filename)) {
                throw FileNotFoundException::forPath($filename);
            }
            if (!@is_file($filename) || !@is_readable($filename)) {
                throw FileNotReadableException::forPath($filename);
            }

            // On POSIX, opening with O_NONBLOCK prevents a substituted FIFO from
            // blocking before fstat() can reject it. Regular files read normally.
            // This is defense in depth, not an atomic path authorization check.
            $handle = @fopen($filename, PHP_OS_FAMILY === 'Windows' ? 'rb' : 'rbn');
            if ($handle === false) {
                throw FileNotReadableException::forPath($filename);
            }

            try {
                $stat = @fstat($handle);
                if ($stat === false || ($stat['mode'] & 0170000) !== 0100000) {
                    throw FileNotReadableException::forPath($filename);
                }
                $content = '';
                $remaining = $this->maxBytes;
                $madeProgress = true;
                while ($remaining > 0 && !feof($handle)) {
                    if (!$madeProgress) {
                        throw new DetectorException('Unable to read file content.');
                    }
                    $chunk = @fread($handle, min(self::READ_CHUNK_BYTES, $remaining));
                    if ($chunk === false) {
                        throw new DetectorException('Unable to read file content.');
                    }
                    $bytes = strlen($chunk);
                    $madeProgress = $bytes !== 0;
                    $content .= $chunk;
                    $remaining -= $bytes;
                }
                return $content;
            } finally {
                @fclose($handle);
            }
        } catch (DetectorException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new DetectorException('Unable to read file content.', previous: $exception);
        }
    }
}
