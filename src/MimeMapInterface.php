<?php

declare(strict_types=1);

namespace Componenta\Detector;

interface MimeMapInterface
{
    /**
     * Get extensions for a MIME type
     *
     * @return list<string>
     */
    public function getExtensions(string $mimeType): array;

    /**
     * Get primary extension for a MIME type
     */
    public function getExtension(string $mimeType): ?string;

    /**
     * Get MIME type for an extension
     */
    public function getMimeType(string $extension): ?string;

    /**
     * Get all MIME types for an extension
     *
     * @return list<string>
     */
    public function getMimeTypes(string $extension): array;

    /**
     * Check if extension is valid for a MIME type
     */
    public function isValidExtension(string $mimeType, string $extension): bool;

    /**
     * Check if MIME type exists in the map
     */
    public function hasMimeType(string $mimeType): bool;

    /**
     * Check if extension exists in the map
     */
    public function hasExtension(string $extension): bool;

    /**
     * Get all registered MIME types
     *
     * @return list<string>
     */
    public function getAllMimeTypes(): array;

    /**
     * Get all registered extensions
     *
     * @return list<string>
     */
    public function getAllExtensions(): array;

    /**
     * Get MIME types by category (image, video, audio, etc.)
     *
     * @return array<string, list<string>>
     */
    public function getMimeTypesByCategory(string $category): array;
}
