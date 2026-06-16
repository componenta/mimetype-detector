<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Psr\Http\Message\StreamInterface;

interface ExtensionDetectorInterface
{
    /**
     * Detect file extension from raw content
     *
     * @param string|StreamInterface $content Raw file content
     * @param bool $asObject If true, returns Ext object instead of string
     * @return ($asObject is true ? Ext|null : string|null)
     *
     * @throws DetectorException
     */
    public function detectExtension(string|StreamInterface $content, bool $asObject = false): string|Ext|null;
}
