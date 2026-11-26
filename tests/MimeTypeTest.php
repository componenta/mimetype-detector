<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\{InvalidMimeTypeException, MimeType};
use Componenta\Detector\MimeTypes\{Application, Video};
use PHPUnit\Framework\TestCase;

final class MimeTypeTest extends TestCase
{
    public function testParsesTypeAndQuotedParameters(): void
    {
        $mime = new MimeType('Text/HTML; charset=UTF-8; name="a;b.txt"; boundary="a\"b"; 123=x');
        self::assertSame('text/html', $mime->value);
        self::assertSame('text', $mime->type);
        self::assertSame('html', $mime->subtype);
        self::assertSame('utf-8', $mime->charset);
        self::assertSame('a;b.txt', $mime->getParameter('NAME'));
        self::assertSame('a"b', $mime->boundary);
        self::assertSame('x', $mime->getParameter('123'));
        self::assertTrue($mime->hasParameters());
        self::assertSame('text/html', (string) $mime);
    }

    public function testSeparatesExactEqualityAndRanges(): void
    {
        $mime = new MimeType('image/jpeg; x=1');
        self::assertTrue($mime->equals('IMAGE/JPEG'));
        self::assertTrue($mime->equals('image/jpeg; x=2'));
        self::assertFalse($mime->equals('image/*', '*/*'));
        self::assertTrue($mime->matches('image/*'));
        self::assertTrue($mime->matches('*/*'));
        self::assertTrue($mime->matches('IMAGE/JPEG'));
        self::assertFalse($mime->matches('video/*', '*/jpeg', 'image/*; q=1'));
        self::assertTrue($mime->isImage());
        self::assertTrue($mime->isMedia());
        self::assertFalse($mime->isText());
        self::assertFalse($mime->isFont());
        self::assertFalse((new MimeType('application/not-a-font'))->isFont());
        self::assertSame(Video::H264, (new MimeType('VIDEO/H264'))->toVideoEnum());
        self::assertSame(Application::Json, (new MimeType('application/json'))->toApplicationEnum());
    }

    public function testValidationAndConstructionAgree(): void
    {
        foreach (['text/html/other', 'text / html', 'image/*', '*/*', 'text/ht ml', "text/html\r\nX: y", 'text/html; broken', 'text/html; x="unclosed', 'text/html; x=1; X=2', 'text/html; x='] as $value) {
            self::assertFalse(MimeType::isValid($value), $value);
            self::assertNull(MimeType::tryCreate($value));
            try {
                new MimeType($value);
                self::fail('Expected InvalidMimeTypeException');
            } catch (InvalidMimeTypeException) {
                self::assertTrue(true);
            }
        }
        foreach (['application/vnd.api+json', 'text/plain; x="";', " \ttext/plain; ; charset=utf-8\t", 'model/gltf+json'] as $value) {
            self::assertTrue(MimeType::isValid($value), $value);
            self::assertInstanceOf(MimeType::class, MimeType::tryCreate($value));
        }
    }
}
