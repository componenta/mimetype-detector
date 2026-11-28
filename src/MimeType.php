<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Exception\InvalidMimeTypeException;
use Componenta\Detector\Internal\MediaTypeParser;
use Componenta\Detector\MimeTypes\{Application, Audio, Font, Image, Message, Model, Multipart, Text, Video};

final readonly class MimeType implements \Stringable
{
    public string $value;
    public string $type;
    public string $subtype;

    public MimeParameters $parameters;
    public string $raw;

    public ?string $charset;
    public ?string $boundary;

    /** @throws InvalidMimeTypeException */
    public function __construct(string $value)
    {
        [$type, $subtype, $parameters] = MediaTypeParser::parse($value);
        $this->raw = $value;
        $this->type = $type;
        $this->subtype = $subtype;
        $this->value = $type . '/' . $subtype;
        $this->parameters = new MimeParameters($parameters);
        $this->charset = $this->parameters->get('charset');
        $this->boundary = $this->parameters->get('boundary');
    }

    public static function tryCreate(string $value): ?self
    {
        try {
            return new self($value);
        } catch (InvalidMimeTypeException) {
            return null;
        }
    }

    public static function isValid(string $value): bool
    {
        return self::tryCreate($value) !== null;
    }

    /** The normalized type/subtype, without parameters. */
    public function __toString(): string
    {
        return $this->value;
    }

    /** Exact equality of normalized type/subtype; parameters are deliberately ignored. */
    public function equals(self|string ...$types): bool
    {
        foreach ($types as $type) {
            $candidate = $type instanceof self ? $type->value : MediaTypeParser::tryNormalize($type);
            if ($candidate === $this->value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match a bare media type, a type wildcard, or the universal wildcard.
     * Not an Accept header parser; parameters and quality weights are not supported.
     */
    public function matches(string ...$ranges): bool
    {
        foreach ($ranges as $range) {
            $range = strtolower(trim($range, " \t"));
            if ($range === '*/*' || $range === $this->type . '/*' || $range === $this->value) {
                return true;
            }
        }

        return false;
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isAudio(): bool
    {
        return $this->type === 'audio';
    }

    public function isText(): bool
    {
        return $this->type === 'text';
    }

    public function isApplication(): bool
    {
        return $this->type === 'application';
    }

    public function isModel(): bool
    {
        return $this->type === 'model';
    }

    public function isMessage(): bool
    {
        return $this->type === 'message';
    }

    public function isMultipart(): bool
    {
        return $this->type === 'multipart';
    }

    public function isFont(): bool
    {
        return $this->type === 'font' || Font::tryFrom($this->value) !== null;
    }

    public function isMedia(): bool
    {
        return $this->isImage() || $this->isVideo() || $this->isAudio();
    }

    public function toImageEnum(): ?Image
    {
        return Image::tryFrom($this->value);
    }

    public function toVideoEnum(): ?Video
    {
        return Video::tryFrom($this->value);
    }

    public function toAudioEnum(): ?Audio
    {
        return Audio::tryFrom($this->value);
    }

    public function toTextEnum(): ?Text
    {
        return Text::tryFrom($this->value);
    }

    public function toApplicationEnum(): ?Application
    {
        return Application::tryFrom($this->value);
    }

    public function toFontEnum(): ?Font
    {
        return Font::tryFrom($this->value);
    }

    public function toMessageEnum(): ?Message
    {
        return Message::tryFrom($this->value);
    }

    public function toModelEnum(): ?Model
    {
        return Model::tryFrom($this->value);
    }

    public function toMultipartEnum(): ?Multipart
    {
        return Multipart::tryFrom($this->value);
    }
}
