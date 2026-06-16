<?php

declare(strict_types=1);

namespace Componenta\Detector;

final readonly class Ext implements \Stringable
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = strtolower(ltrim($value, '.'));
    }

    /**
     * Try to detect extension from content
     */
    public static function tryFrom(string $content, ?ExtensionDetectorInterface $detector = null): ?self
    {
        $detector ??= new FinfoDetector();
        $extension = $detector->detectExtension($content);

        return $extension !== null ? new self($extension) : null;
    }

    /**
     * Try to detect extension from file
     * 
     * @throws FileNotFoundException if file does not exist
     * @throws FileNotReadableException if file cannot be read
     * @throws DetectorException on other detection errors
     */
    public static function tryFromFile(string $filename, ?FileExtensionDetectorInterface $detector = null): ?self
    {
        $detector ??= new FinfoDetector();
        $extension = $detector->detectFileExtension($filename);

        return $extension !== null ? new self($extension) : null;
    }

    /**
     * Create from MIME type using MimeMap
     */
    public static function fromMimeType(string $mimeType, ?MimeMapInterface $mimeMap = null): ?self
    {
        $mimeMap ??= new MimeMap();
        $extension = $mimeMap->getExtension($mimeType);

        return $extension !== null ? new self($extension) : null;
    }

    /**
     * Get all extensions for a MIME type
     * 
     * @return list<self>
     */
    public static function allFromMimeType(string $mimeType, ?MimeMapInterface $mimeMap = null): array
    {
        $mimeMap ??= new MimeMap();
        
        return array_map(
            static fn(string $ext) => new self($ext),
            $mimeMap->getExtensions($mimeType)
        );
    }

    /**
     * Convert to MimeType (primary MIME type for this extension)
     */
    public function toMimeType(?MimeMapInterface $mimeMap = null): ?MimeType
    {
        return MimeType::fromExtension($this->value, $mimeMap);
    }

    /**
     * Get all MIME types for this extension
     * 
     * @return list<MimeType>
     */
    public function toAllMimeTypes(?MimeMapInterface $mimeMap = null): array
    {
        $mimeMap ??= new MimeMap();
        
        return array_map(
            static fn(string $mime) => new MimeType($mime),
            $mimeMap->getMimeTypes($this->value)
        );
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Check if extension matches any of the given extensions
     */
    public function equals(self|string ...$extensions): bool
    {
        foreach ($extensions as $ext) {
            if (strtolower(ltrim((string) $ext, '.')) === $this->value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if extension is one of common image extensions
     */
    public function isImage(): bool
    {
        return $this->equals('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'tiff', 'avif', 'heic');
    }

    /**
     * Check if extension is one of common video extensions
     */
    public function isVideo(): bool
    {
        return $this->equals('mp4', 'webm', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'mpeg', 'mpg', '3gp');
    }

    /**
     * Check if extension is one of common audio extensions
     */
    public function isAudio(): bool
    {
        return $this->equals('mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'wma', 'opus');
    }

    /**
     * Check if extension is one of common document extensions
     */
    public function isDocument(): bool
    {
        return $this->equals('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'rtf', 'txt');
    }

    /**
     * Check if extension is one of common archive extensions
     */
    public function isArchive(): bool
    {
        return $this->equals('zip', 'rar', 'tar', 'gz', '7z', 'bz2', 'xz');
    }

    /**
     * Get extension with leading dot
     */
    public function withDot(): string
    {
        return '.' . $this->value;
    }
}
