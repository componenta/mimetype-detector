<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\Ext;
use Componenta\Detector\FileNotFoundException;
use Componenta\Detector\FinfoDetector;
use Componenta\Detector\MimeType;
use PHPUnit\Framework\TestCase;

final class FinfoDetectorTest extends TestCase
{
    public function testDetectsMimeTypeFromContent(): void
    {
        $detector = new FinfoDetector();

        self::assertSame('text/plain', $detector->detectMimeType('plain text'));
        self::assertInstanceOf(MimeType::class, $detector->detectMimeType('plain text', asObject: true));
    }

    public function testDetectsExtensionFromContent(): void
    {
        $detector = new FinfoDetector();

        self::assertSame('txt', $detector->detectExtension('plain text'));
        self::assertInstanceOf(Ext::class, $detector->detectExtension('plain text', asObject: true));
    }

    public function testThrowsWhenFileDoesNotExist(): void
    {
        $this->expectException(FileNotFoundException::class);

        (new FinfoDetector())->detectFileMimeType(__DIR__ . '/missing.txt');
    }
}
