<?php

declare(strict_types=1);

namespace Componenta\Detector;

use finfo;
use Psr\Http\Message\StreamInterface;

final readonly class FinfoDetector implements DetectorInterface
{
    private finfo $finfo;
    private MimeMapInterface $mimeMap;

    public function __construct(?MimeMapInterface $mimeMap = null)
    {
        $this->finfo = new finfo();
        $this->mimeMap = $mimeMap ?? new MimeMap();
    }

    /**
     * @inheritDoc
     */
    public function detectFileMimeType(string $filename, bool $asObject = false): string|MimeType|null
    {
        $mimeType = $this->detectMimeType($this->readFile($filename));

        if ($mimeType === null) {
            return null;
        }

        return $asObject ? new MimeType($mimeType) : $mimeType;
    }

    /**
     * @inheritDoc
     */
    public function detectMimeType(string|StreamInterface $content, bool $asObject = false): string|MimeType|null
    {
        if ($content instanceof StreamInterface) {
            $content = $this->readStream($content);
        }

        if ($content === '') {
            return null;
        }

        $mimeType = $this->finfo->buffer($content, FILEINFO_MIME_TYPE);

        if ($mimeType === false || $mimeType === '???' || $mimeType === 'application/x-empty') {
            return null;
        }

        $semicolonPos = strpos($mimeType, ';');

        if ($semicolonPos !== false) {
            $mimeType = trim(substr($mimeType, 0, $semicolonPos));
        }

        return $asObject ? new MimeType($mimeType) : $mimeType;
    }

    /**
     * @inheritDoc
     */
    public function detectFileExtension(string $filename, bool $asObject = false): string|Ext|null
    {
        $content = $this->readFile($filename);
        $extension = $this->detectExtensionFromContent($content);

        if ($extension === null) {
            $extensions = $this->getExtensions($content);

            if ($extensions === []) {
                return null;
            }

            $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            $extension = in_array($fileExt, $extensions, true)
                ? $fileExt
                : $extensions[0];
        }

        return $asObject ? new Ext($extension) : $extension;
    }

    /**
     * @inheritDoc
     */
    public function detectExtension(string|StreamInterface $content, bool $asObject = false): string|Ext|null
    {
        if ($content instanceof StreamInterface) {
            $content = $this->readStream($content);
        }

        $extension = $this->detectExtensionFromContent($content);

        if ($extension === null) {
            $extension = $this->getExtensions($content)[0] ?? null;

            if ($extension === null) {
                return null;
            }
        }

        return $asObject ? new Ext($extension) : $extension;
    }

    /**
     * Reads stream content preserving position for seekable streams
     *
     * Warning: For non-seekable streams, reads from current position.
     * Ensure stream is at position 0 or use seekable streams for reliable detection.
     *
     * @throws DetectorException
     */
    private function readStream(StreamInterface $stream): string
    {
        if (!$stream->isReadable()) {
            throw new DetectorException('Stream is not readable');
        }

        $position = null;

        if ($stream->isSeekable()) {
            $position = $stream->tell();
            $stream->rewind();
        }

        try {
            $content = $stream->read(8192);
        } catch (\Throwable $e) {
            throw new DetectorException('Unable to read stream content', previous: $e);
        } finally {
            if ($position !== null && $stream->isSeekable()) {
                $stream->seek($position);
            }
        }

        return $content;
    }

    /**
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws DetectorException
     */
    private function readFile(string $filename): string
    {
        if (!file_exists($filename)) {
            throw FileNotFoundException::forPath($filename);
        }

        if (!is_readable($filename)) {
            throw FileNotReadableException::forPath($filename);
        }

        $handle = @fopen($filename, 'rb');

        if ($handle === false) {
            throw new DetectorException("Cannot open file: {$filename}");
        }

        try {
            $content = fread($handle, 8192);

            if ($content === false) {
                throw new DetectorException("Cannot read file: {$filename}");
            }

            return $content;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Detects extension using finfo FILEINFO_EXTENSION
     */
    private function detectExtensionFromContent(string $content): ?string
    {
        if ($content === '') {
            return null;
        }

        $extension = $this->finfo->buffer($content, FILEINFO_EXTENSION);

        if ($extension === false || $extension === '???') {
            return null;
        }

        if (str_contains($extension, '/')) {
            $extension = strstr($extension, '/', true);
        }

        return $extension ?: null;
    }

    /**
     * @return list<string>
     */
    private function getExtensions(string $content): array
    {
        $mimeType = $this->detectMimeType($content);

        if ($mimeType === null) {
            return [];
        }

        return $this->mimeMap->getExtensions($mimeType);
    }
}
