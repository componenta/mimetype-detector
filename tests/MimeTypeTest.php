<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\InvalidMimeTypeException;
use Componenta\Detector\MimeType;
use PHPUnit\Framework\TestCase;

final class MimeTypeTest extends TestCase
{
    public function testParsesMimeTypeAndParameters(): void
    {
        $mimeType = new MimeType('Text/HTML; charset=utf-8; boundary="abc"');

        self::assertSame('text/html', $mimeType->value);
        self::assertSame('text', $mimeType->type);
        self::assertSame('html', $mimeType->subtype);
        self::assertSame('Text/HTML; charset=utf-8; boundary="abc"', $mimeType->raw);
        self::assertSame('utf-8', $mimeType->charset);
        self::assertSame('abc', $mimeType->boundary);
        self::assertTrue($mimeType->hasParameters());
    }

    public function testSupportsMatchingAndCategoryHelpers(): void
    {
        $mimeType = new MimeType('image/jpeg');

        self::assertTrue($mimeType->equals('image/jpeg'));
        self::assertTrue($mimeType->equals('image/*'));
        self::assertTrue($mimeType->contains('jpeg'));
        self::assertTrue($mimeType->isImage());
        self::assertTrue($mimeType->isMedia());
        self::assertTrue($mimeType->isWebSafe());
        self::assertTrue($mimeType->isBinary());
        self::assertFalse($mimeType->isText());
    }

    public function testRejectsInvalidMimeType(): void
    {
        $this->expectException(InvalidMimeTypeException::class);

        new MimeType('plain-text');
    }
}
