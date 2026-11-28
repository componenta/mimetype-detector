<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Exception\DetectorConfigurationException;
use Componenta\Detector\Exception\DetectorException;
use Componenta\Detector\Exception\DetectorExceptionInterface;
use Componenta\Detector\Exception\FileExceptionInterface;
use Componenta\Detector\Exception\StreamExceptionInterface;
use Componenta\Detector\Internal\FileReader;
use Componenta\Detector\Internal\StreamReader;
use finfo;
use Psr\Http\Message\StreamInterface;

final readonly class FinfoDetector implements DetectorInterface
{
    private finfo $finfo;
    private MimeMapInterface $mimeMap;

    /** @throws DetectorConfigurationException */
    public function __construct(?MimeMapInterface $mimeMap = null, private int $maxBytes = 8192)
    {
        if ($maxBytes < 1) {
            throw new DetectorConfigurationException('The detection byte limit must be positive.');
        }

        $this->finfo = new finfo(FILEINFO_MIME_TYPE);
        $this->mimeMap = $mimeMap ?? new MimeMap();
    }

    /**
     * @throws DetectorException
     * @throws StreamExceptionInterface When the source is a stream.
     */
    public function detectMimeType(string|StreamInterface $content): ?MimeType
    {
        return $this->detectBuffer(
            $content instanceof StreamInterface ? StreamReader::readPrefix($content, $this->maxBytes) : substr($content, 0, $this->maxBytes),
        );
    }

    /**
     * @throws DetectorException
     * @throws FileExceptionInterface
     */
    public function detectFileMimeType(string $filename): ?MimeType
    {
        return $this->detectBuffer(FileReader::readPrefix($filename, $this->maxBytes));
    }

    /** @inheritDoc */
    public function detectExtension(string|StreamInterface $content): ?Ext
    {
        return $this->resolveExtension($this->detectMimeType($content));
    }

    /** @inheritDoc */
    public function detectFileExtension(string $filename): ?Ext
    {
        return $this->resolveExtension($this->detectFileMimeType($filename));
    }

    /** @throws DetectorException */
    private function detectBuffer(string $buffer): ?MimeType
    {
        if ($buffer === '') {
            return null;
        }

        try {
            $value = @$this->finfo->buffer($buffer);
        } catch (\ErrorException|\ValueError $error) {
            throw new DetectorException('Unable to classify content.', previous: $error);
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

    /** @throws DetectorExceptionInterface */
    private function resolveExtension(?MimeType $mime): ?Ext
    {
        if ($mime === null) {
            return null;
        }

        try {
            $extension = $this->mimeMap->getExtension($mime->value);
        } catch (DetectorExceptionInterface $error) {
            throw $error;
        } catch (\RuntimeException $error) {
            throw new DetectorException('Unable to resolve a mapped extension.', previous: $error);
        }

        if ($extension === null) {
            return null;
        }

        return Ext::tryCreate($extension)
            ?? throw new DetectorException('The MIME map returned an invalid extension.');
    }
}
