<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\MimeTypes\Application;
use Componenta\Detector\MimeTypes\Audio;
use Componenta\Detector\MimeTypes\Font;
use Componenta\Detector\MimeTypes\Image;
use Componenta\Detector\MimeTypes\Message;
use Componenta\Detector\MimeTypes\Model;
use Componenta\Detector\MimeTypes\Multipart;
use Componenta\Detector\MimeTypes\Text;
use Componenta\Detector\MimeTypes\Video;

final class MimeType implements \Stringable
{
    /**
     * Full MIME type value without parameters (e.g., 'text/html')
     */
    public readonly string $value;
    
    /**
     * Primary type (e.g., 'text', 'image', 'application')
     */
    public readonly string $type;
    
    /**
     * Subtype without parameters (e.g., 'html', 'jpeg')
     */
    public readonly string $subtype;
    
    /**
     * Parameters (e.g., ['charset' => 'utf-8'])
     * @var array<string, string>
     */
    public readonly array $parameters;
    
    /**
     * Original raw value including parameters
     */
    public readonly string $raw;

    /**
     * Charset parameter if present
     */
    public ?string $charset {
        get => $this->parameters['charset'] ?? null;
    }

    /**
     * Boundary parameter if present (for multipart types)
     */
    public ?string $boundary {
        get => $this->parameters['boundary'] ?? null;
    }

    public function __construct(string $value)
    {
        $this->raw = $value;
        
        // Split MIME type and parameters: "text/html; charset=utf-8" -> ["text/html", "charset=utf-8"]
        $parts = array_map('trim', explode(';', $value));
        $mimeType = strtolower(array_shift($parts));
        
        // Validate MIME type format
        if (!str_contains($mimeType, '/')) {
            throw InvalidMimeTypeException::forValue($value);
        }
        
        $this->value = $mimeType;
        
        // Parse type/subtype
        $typeParts = explode('/', $mimeType, 2);
        $this->type = $typeParts[0];
        $this->subtype = $typeParts[1];
        
        // Validate type and subtype are not empty
        if ($this->type === '' || $this->subtype === '') {
            throw InvalidMimeTypeException::forValue($value);
        }
        
        // Parse parameters
        $parameters = [];
        foreach ($parts as $part) {
            $paramParts = explode('=', $part, 2);
            if (count($paramParts) === 2) {
                $key = strtolower(trim($paramParts[0]));
                $val = trim($paramParts[1], " \t\n\r\0\x0B\"'");
                $parameters[$key] = $val;
            }
        }
        $this->parameters = $parameters;
    }

    /**
     * Check if MIME type has parameters
     */
    public function hasParameters(): bool
    {
        return $this->parameters !== [];
    }

    /**
     * Get a specific parameter
     */
    public function getParameter(string $name): ?string
    {
        return $this->parameters[strtolower($name)] ?? null;
    }

    /**
     * Check if a string is a valid MIME type format
     */
    public static function isValid(string $value): bool
    {
        $parts = explode(';', $value, 2);
        $mimeType = strtolower(trim($parts[0]));
        
        if (!str_contains($mimeType, '/')) {
            return false;
        }
        
        $typeParts = explode('/', $mimeType, 2);
        
        return $typeParts[0] !== '' && $typeParts[1] !== '';
    }

    /**
     * Create MimeType instance or return null if invalid
     */
    public static function tryCreate(string $value): ?self
    {
        if (!self::isValid($value)) {
            return null;
        }
        
        return new self($value);
    }

    /**
     * Try to detect MIME type from content
     */
    public static function tryFrom(string $content, ?MimeTypeDetectorInterface $detector = null): ?self
    {
        $detector ??= new FinfoDetector();
        $mimeType = $detector->detectMimeType($content);

        return $mimeType !== null ? new self($mimeType) : null;
    }

    /**
     * Try to detect MIME type from file
     * 
     * @throws FileNotFoundException if file does not exist
     * @throws FileNotReadableException if file cannot be read
     * @throws DetectorException on other detection errors
     */
    public static function tryFromFile(string $filename, ?FileMimeTypeDetectorInterface $detector = null): ?self
    {
        $detector ??= new FinfoDetector();
        $mimeType = $detector->detectFileMimeType($filename);

        return $mimeType !== null ? new self($mimeType) : null;
    }

    /**
     * Create from extension using MimeMap
     */
    public static function fromExtension(string $extension, ?MimeMapInterface $mimeMap = null): ?self
    {
        $mimeMap ??= new MimeMap();
        $mimeType = $mimeMap->getMimeType($extension);

        return $mimeType !== null ? new self($mimeType) : null;
    }

    /**
     * Convert to Ext (primary extension for this MIME type)
     */
    public function toExt(?MimeMapInterface $mimeMap = null): ?Ext
    {
        return Ext::fromMimeType($this->value, $mimeMap);
    }

    /**
     * Get all extensions for this MIME type
     * 
     * @return list<Ext>
     */
    public function toAllExt(?MimeMapInterface $mimeMap = null): array
    {
        return Ext::allFromMimeType($this->value, $mimeMap);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Check if MIME type matches any of the given types
     * Supports wildcards: 'image/*' matches any image type
     */
    public function equals(self|string ...$types): bool
    {
        foreach ($types as $type) {
            $type = strtolower((string) $type);
            
            if ($type === $this->value) {
                return true;
            }
            
            // Wildcard support: image/* matches image/jpeg, image/png, etc.
            if (str_ends_with($type, '/*')) {
                $needle = substr($type, 0, -2);
                if ($this->type === $needle) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if MIME type contains the given string
     */
    public function contains(string $needle): bool
    {
        return str_contains($this->value, strtolower($needle));
    }

    /**
     * Check if this is an image MIME type
     */
    public function isImage(): bool
    {
        return $this->type === 'image' || Image::tryFrom($this->value) !== null;
    }

    /**
     * Check if this is a video MIME type
     */
    public function isVideo(): bool
    {
        return $this->type === 'video' || Video::tryFrom($this->value) !== null;
    }

    /**
     * Check if this is an audio MIME type
     */
    public function isAudio(): bool
    {
        return $this->type === 'audio' || Audio::tryFrom($this->value) !== null;
    }

    /**
     * Check if this is a text MIME type
     */
    public function isText(): bool
    {
        return $this->type === 'text' || Text::tryFrom($this->value) !== null;
    }

    /**
     * Check if this is an application MIME type
     */
    public function isApplication(): bool
    {
        return $this->type === 'application' || Application::tryFrom($this->value) !== null;
    }

    /**
     * Check if this is a font MIME type
     */
    public function isFont(): bool
    {
        return $this->type === 'font' 
            || Font::tryFrom($this->value) !== null 
            || str_contains($this->value, 'font');
    }

    /**
     * Check if this is a model (3D) MIME type
     */
    public function isModel(): bool
    {
        return $this->type === 'model' || Model::tryFrom($this->value) !== null;
    }

    /**
     * Check if this is a message MIME type
     */
    public function isMessage(): bool
    {
        return $this->type === 'message' || Message::tryFrom($this->value) !== null;
    }

    /**
     * Check if this is a multipart MIME type
     */
    public function isMultipart(): bool
    {
        return $this->type === 'multipart' || Multipart::tryFrom($this->value) !== null;
    }

    /**
     * Check if this is a media MIME type (image, video, or audio)
     */
    public function isMedia(): bool
    {
        return $this->isImage() || $this->isVideo() || $this->isAudio();
    }

    /**
     * Check if this MIME type is safe for web display
     */
    public function isWebSafe(): bool
    {
        return match (true) {
            $this->equals(
                'text/html',
                'text/plain',
                'text/css',
                'text/javascript',
                'application/javascript',
                'application/json',
                'application/xml',
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/svg+xml',
                'image/avif',
                'video/mp4',
                'video/webm',
                'audio/mpeg',
                'audio/ogg',
                'audio/webm',
                'font/woff',
                'font/woff2',
            ) => true,
            default => false,
        };
    }

    /**
     * Check if this MIME type represents binary content
     */
    public function isBinary(): bool
    {
        return !$this->isText() 
            && !$this->equals(
                'application/json',
                'application/xml',
                'application/javascript',
                'application/xhtml+xml',
            );
    }

    /**
     * Check if this MIME type is compressible
     */
    public function isCompressible(): bool
    {
        return $this->isText()
            || $this->equals(
                'application/json',
                'application/xml',
                'application/javascript',
                'application/xhtml+xml',
                'image/svg+xml',
                'application/rss+xml',
                'application/atom+xml',
            );
    }

    /**
     * Try to match this MIME type to an Image enum case
     */
    public function toImageEnum(): ?Image
    {
        return Image::tryFrom($this->value);
    }

    /**
     * Try to match this MIME type to a Video enum case
     */
    public function toVideoEnum(): ?Video
    {
        return Video::tryFrom($this->value);
    }

    /**
     * Try to match this MIME type to an Audio enum case
     */
    public function toAudioEnum(): ?Audio
    {
        return Audio::tryFrom($this->value);
    }

    /**
     * Try to match this MIME type to a Text enum case
     */
    public function toTextEnum(): ?Text
    {
        return Text::tryFrom($this->value);
    }

    /**
     * Try to match this MIME type to an Application enum case
     */
    public function toApplicationEnum(): ?Application
    {
        return Application::tryFrom($this->value);
    }
}
