<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Exception\DetectorExceptionInterface;

/** Resolves an extension association, not proof of content or file safety. */
interface ExtensionResolverInterface
{
    /**
     * Resolve the preferred normalized extension for a concrete MIME type.
     * Parameters are ignored. Invalid or unmapped types return null.
     *
     * @throws DetectorExceptionInterface If an external mapping backend fails.
     *     Implementations translate expected backend failures to this contract.
     */
    public function getExtension(string $mimeType): ?string;
}
