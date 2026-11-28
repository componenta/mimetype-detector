<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Exception\DetectorExceptionInterface;

/** Read-only lookup contract; mappings are not evidence of content or file safety. */
interface MimeMapInterface
{
    /** @return list<string> Ordered normalized extensions; unknown or unmapped types yield an empty list. */
    public function getExtensions(string $mimeType): array;

    /**
     * Preferred extension, or null when none is mapped.
     * @throws DetectorExceptionInterface If an external mapping backend fails.
     */
    public function getExtension(string $mimeType): ?string;

    /** First associated MIME type, or null when none is mapped. */
    public function getMimeType(string $extension): ?string;

    /** @return list<string> All associated MIME types in preference order. */
    public function getMimeTypes(string $extension): array;

    /** Whether the supplied extension is registered for the supplied media type. */
    public function isValidExtension(string $mimeType, string $extension): bool;

    /** Whether the media type is registered, even if its extension list is empty. */
    public function hasMimeType(string $mimeType): bool;

    /** Whether at least one MIME type is associated with this extension. */
    public function hasExtension(string $extension): bool;

    /** @return list<string> Registered concrete media type names in map order. */
    public function getAllMimeTypes(): array;

    /** @return list<string> Unique extensions in first-registration order, including numeric extensions as strings. */
    public function getAllExtensions(): array;

    /** @return array<string, list<string>> Mappings with the given top-level category. */
    public function getMimeTypesByCategory(string $category): array;
}
