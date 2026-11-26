<?php

declare(strict_types=1);

namespace Componenta\Detector;

final class MimeMap implements MimeMapInterface
{
    /** @var array<string, list<string>> */
    private array $mimeToExt;

    /** @var array<int|string, list<string>>|null PHP coerces numeric keys to integers. */
    private ?array $extensionIndex = null;

    public function __construct()
    {
        $this->mimeToExt = self::getDefaultMap();
    }

    /**
     * Add or merge mappings atomically. Existing MIME and extension order is retained.
     *
     * @param array<string, list<string>> $map
     */
    public function extend(array $map, bool $override = false): self
    {
        $normalized = self::normalizeMap($map);
        $next = $this->mimeToExt;

        foreach ($normalized as $mimeType => $extensions) {
            $next[$mimeType] = $override || !isset($next[$mimeType])
                ? $extensions
                : array_values(array_unique([...$next[$mimeType], ...$extensions]));
        }

        $this->replace($next);
        return $this;
    }

    public function remove(string ...$mimeTypes): self
    {
        $next = $this->mimeToExt;
        foreach ($mimeTypes as $mimeType) {
            $key = (new MimeType($mimeType))->value;
            unset($next[$key]);
        }
        $this->replace($next);
        return $this;
    }

    /** @param array<string, list<string>> $map */
    public function setMap(array $map): self
    {
        $this->replace(self::normalizeMap($map));
        return $this;
    }

    /** @param array<string, list<string>> $map */
    private function replace(array $map): void
    {
        $this->mimeToExt = $map;
        $this->extensionIndex = null;
    }

    /**
     * @param array<array-key, mixed> $map
     * @return array<string, list<string>>
     */
    private static function normalizeMap(array $map): array
    {
        $result = [];
        foreach ($map as $mimeType => $extensions) {
            if (!is_string($mimeType) || !is_array($extensions) || !array_is_list($extensions)) {
                throw new \InvalidArgumentException('Expected a MIME type mapped to a list of extensions.');
            }
            $key = (new MimeType($mimeType))->value;
            if (array_key_exists($key, $result)) {
                throw new \InvalidArgumentException('Duplicate normalized MIME type.');
            }
            $values = [];
            foreach ($extensions as $extension) {
                if (!is_string($extension)) {
                    throw new \InvalidArgumentException('Expected a string extension.');
                }
                $values[] = (new Ext($extension))->value;
            }
            $result[$key] = array_values(array_unique($values));
        }
        return $result;
    }

    public function getExtensions(string $mimeType): array
    {
        if (isset($this->mimeToExt[$mimeType])) {
            return $this->mimeToExt[$mimeType];
        }
        $key = Internal\MediaTypeParser::tryNormalize($mimeType);
        return $key === null ? [] : ($this->mimeToExt[$key] ?? []);
    }

    public function getExtension(string $mimeType): ?string
    {
        return $this->getExtensions($mimeType)[0] ?? null;
    }

    public function getMimeType(string $extension): ?string
    {
        return $this->getMimeTypes($extension)[0] ?? null;
    }

    public function getMimeTypes(string $extension): array
    {
        $index = $this->getExtensionIndex();
        if (isset($index[$extension])) {
            return $index[$extension];
        }
        $key = Ext::tryCreate($extension)?->value;
        return $key === null ? [] : ($index[$key] ?? []);
    }

    public function isValidExtension(string $mimeType, string $extension): bool
    {
        $key = Ext::tryCreate($extension)?->value;
        return $key !== null && in_array($key, $this->getExtensions($mimeType), true);
    }

    public function hasMimeType(string $mimeType): bool
    {
        $key = Internal\MediaTypeParser::tryNormalize($mimeType);
        return $key !== null && array_key_exists($key, $this->mimeToExt);
    }

    public function hasExtension(string $extension): bool
    {
        return $this->getMimeTypes($extension) !== [];
    }

    public function getAllMimeTypes(): array
    {
        return array_keys($this->mimeToExt);
    }

    public function getAllExtensions(): array
    {
        return array_map(static fn (int|string $key): string => (string) $key, array_keys($this->getExtensionIndex()));
    }

    public function getMimeTypesByCategory(string $category): array
    {
        $prefix = strtolower(trim($category, " \t")) . '/';
        return array_filter(
            $this->mimeToExt,
            static fn (string $mime): bool => str_starts_with($mime, $prefix),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /** @return array<int|string, list<string>> */
    private function getExtensionIndex(): array
    {
        if ($this->extensionIndex === null) {
            $index = [];
            foreach ($this->mimeToExt as $mime => $extensions) {
                foreach ($extensions as $extension) {
                    $index[$extension][] = $mime;
                }
            }
            $this->extensionIndex = $index;
        }
        return $this->extensionIndex;
    }

    /**
     * Local extension associations; not an exhaustive registry of media types.
     *
     * @return array<string, list<string>>
     */
    public static function getDefaultMap(): array
    {
        return Internal\DefaultMimeMap::MAPPINGS;
    }
}
