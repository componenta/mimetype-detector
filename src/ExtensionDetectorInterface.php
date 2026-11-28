<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Exception\DetectorExceptionInterface;
use Componenta\Detector\Exception\StreamExceptionInterface;
use Psr\Http\Message\StreamInterface;

interface ExtensionDetectorInterface
{
    /**
     * Resolve a preferred extension for the detected content; null means no result.
     * Strings contain raw bytes; seekable streams preserve their position. Non-seekable streams consume the inspected prefix. Stream failures only apply to stream input.
     *
     * @throws DetectorExceptionInterface
     * @throws StreamExceptionInterface When the source is a stream.
     */
    public function detectExtension(string|StreamInterface $content): ?Ext;
}
