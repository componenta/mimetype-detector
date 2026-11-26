<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Psr\Http\Message\StreamInterface;

interface ExtensionDetectorInterface
{
    /**
     * Resolve a preferred extension for the detected content; null means no result.
     * Strings contain raw bytes; seekable streams preserve their position. Non-seekable streams are consumed.
     *
     * @throws DetectorException
     */
    public function detectExtension(string|StreamInterface $content): ?Ext;
}
