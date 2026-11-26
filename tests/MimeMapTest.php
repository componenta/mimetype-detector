<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\{Ext, MimeMap, MimeType};
use PHPUnit\Framework\Assert;

test('default mappings are valid and reciprocal', function (): void {
    $map = new MimeMap();
    Assert::assertSame('jpg', $map->getExtension('image/jpeg'));
    Assert::assertSame('image/jpeg', $map->getMimeType('.JPG'));
    Assert::assertSame('xml', $map->getExtension('text/xml'));
    Assert::assertSame('application/xml', $map->getMimeType('xml'));
    Assert::assertContains('text/xml', $map->getMimeTypes('xml'));
    Assert::assertContains('264', $map->getAllExtensions());
    foreach ($map->getAllExtensions() as $extension) {
        Assert::assertIsString($extension);
        Assert::assertTrue($map->hasExtension($extension));
        Assert::assertSame($map->getMimeTypes($extension)[0], $map->getMimeType($extension));
    }
    foreach ($map->getAllMimeTypes() as $mime) {
        Assert::assertTrue(MimeType::isValid($mime), $mime);
        foreach ($map->getExtensions($mime) as $ext) {
            Assert::assertSame($ext, (new Ext($ext))->value);
            Assert::assertContains($mime, $map->getMimeTypes($ext));
        }
    }
});

test('normalizes mutations and preserves order', function (): void {
    $map = new MimeMap();
    $map->setMap(['TEXT/X-TEST' => ['.TEST', 'test', 'other'], 'text/x-alt' => ['test']]);
    Assert::assertSame(['test', 'other'], $map->getExtensions('text/x-test; charset=UTF-8'));
    Assert::assertSame(['text/x-test', 'text/x-alt'], $map->getMimeTypes('.TEST'));
    Assert::assertSame('text/x-test', $map->getMimeType('test'));
    $map->extend(['text/x-test' => ['NEW']]);
    Assert::assertSame(['test', 'other', 'new'], $map->getExtensions('text/x-test'));
    $map->extend(['text/x-test' => ['replacement']], override: true);
    Assert::assertSame('text/x-alt', $map->getMimeType('test'));
    $map->remove('TEXT/X-ALT');
    Assert::assertNull($map->getMimeType('test'));
    Assert::assertSame(['replacement'], $map->getAllExtensions());
});

test('invalid mutations are atomic with cold and warm indexes', function (): void {
    foreach ([false, true] as $warm) {
        foreach (['extend', 'setMap'] as $method) {
            $map = (new MimeMap())->setMap(['text/x-original' => ['orig']]);
            if ($warm) {
                Assert::assertSame('text/x-original', $map->getMimeType('orig'));
            }
            try {
                $map->$method(['text/x-new' => ['new'], 'invalid' => ['bad']]);
                Assert::fail('Expected invalid MIME type');
            } catch (\InvalidArgumentException) {
                Assert::assertSame(['text/x-original'], $map->getAllMimeTypes());
                Assert::assertSame(['orig'], $map->getAllExtensions());
                Assert::assertNull($map->getMimeType('new'));
                Assert::assertSame('text/x-original', $map->getMimeType('orig'));
            }
        }
    }
});

test('rejects non list and invalid extensions', function (): void {
    foreach ([['primary' => 'foo'], [42], ['../foo']] as $extensions) {
        try {
            (new MimeMap())->extend(['text/x-test' => $extensions]);
            Assert::fail('Expected invalid map');
        } catch (\InvalidArgumentException) {
            Assert::assertTrue(true);
        }
    }
});

test('maps observed fileinfo labels and preserves preferred reverse associations', function (): void {
    $map = new MimeMap();
    Assert::assertSame('rtf', $map->getExtension('text/rtf'));
    Assert::assertSame('application/rtf', $map->getMimeType('rtf'));
    Assert::assertContains('text/rtf', $map->getMimeTypes('rtf'));
    Assert::assertSame('py', $map->getExtension('text/x-script.python'));
    Assert::assertSame('text/x-python', $map->getMimeType('py'));
    Assert::assertSame('otf', $map->getExtension('application/vnd.ms-opentype'));
    Assert::assertSame(['otf', 'ttf'], $map->getExtensions('font/sfnt'));
    Assert::assertSame(['otf', 'ttf'], $map->getExtensions('application/font-sfnt'));
    Assert::assertSame('font/otf', $map->getMimeType('otf'));
    Assert::assertSame('font/ttf', $map->getMimeType('ttf'));
});

test('prefers the registered DWG media type and retains its legacy association', function (): void {
    $map = new MimeMap();
    Assert::assertSame('dwg', $map->getExtension('image/vnd.dwg'));
    Assert::assertSame('dwg', $map->getExtension('application/vnd.autocad.dwg'));
    Assert::assertSame('image/vnd.dwg', $map->getMimeType('dwg'));
    Assert::assertSame(['image/vnd.dwg', 'application/vnd.autocad.dwg'], $map->getMimeTypes('dwg'));
});
