<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\Exception\InvalidMimeTypeException;
use Componenta\Detector\MimeType;
use Componenta\Detector\MimeTypes\{Application, Video};
use PHPUnit\Framework\Assert;

test('parses type and quoted parameters', function (): void {
    $mime = new MimeType('Text/HTML; charset=UTF-8; name="a;b.txt"; boundary="a\"b"; 123=x');
    Assert::assertSame('text/html', $mime->value);
    Assert::assertSame('text', $mime->type);
    Assert::assertSame('html', $mime->subtype);
    Assert::assertSame('utf-8', $mime->charset);
    Assert::assertSame('a;b.txt', $mime->getParameter('NAME'));
    Assert::assertSame('a"b', $mime->boundary);
    Assert::assertSame('x', $mime->getParameter('123'));
    Assert::assertTrue($mime->hasParameters());
    Assert::assertSame('text/html', (string) $mime);
});

test('separates exact equality and ranges', function (): void {
    $mime = new MimeType('image/jpeg; x=1');
    Assert::assertTrue($mime->equals('IMAGE/JPEG'));
    Assert::assertTrue($mime->equals('image/jpeg; x=2'));
    Assert::assertFalse($mime->equals('image/*', '*/*'));
    Assert::assertTrue($mime->matches('image/*'));
    Assert::assertTrue($mime->matches('*/*'));
    Assert::assertTrue($mime->matches('IMAGE/JPEG'));
    Assert::assertFalse($mime->matches('video/*', '*/jpeg', 'image/*; q=1'));
    Assert::assertTrue($mime->isImage());
    Assert::assertTrue($mime->isMedia());
    Assert::assertFalse($mime->isText());
    Assert::assertFalse($mime->isFont());
    Assert::assertFalse((new MimeType('application/not-a-font'))->isFont());
    Assert::assertSame(Video::H264, (new MimeType('VIDEO/H264'))->toVideoEnum());
    Assert::assertSame(Application::Json, (new MimeType('application/json'))->toApplicationEnum());
});

test('validation and construction agree', function (): void {
    foreach (['text/html/other', 'text / html', 'image/*', '*/*', 'text/ht ml', "text/html\r\nX: y", 'text/html; broken', 'text/html; x="unclosed', 'text/html; x=1; X=2', 'text/html; x='] as $value) {
        Assert::assertFalse(MimeType::isValid($value), $value);
        Assert::assertNull(MimeType::tryCreate($value));
        try {
            new MimeType($value);
            Assert::fail('Expected InvalidMimeTypeException');
        } catch (InvalidMimeTypeException) {
            Assert::assertTrue(true);
        }
    }
    foreach (['application/vnd.api+json', 'text/plain; x="";', " \ttext/plain; ; charset=utf-8\t", 'model/gltf+json'] as $value) {
        Assert::assertTrue(MimeType::isValid($value), $value);
        Assert::assertInstanceOf(MimeType::class, MimeType::tryCreate($value));
    }
});
