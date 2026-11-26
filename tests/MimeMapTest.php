<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\{Ext, MimeMap, MimeType};
use PHPUnit\Framework\TestCase;

final class MimeMapTest extends TestCase
{
    public function testDefaultMappingsAreValidAndReciprocal(): void
    {
        $map = new MimeMap();
        self::assertSame('jpg', $map->getExtension('image/jpeg'));
        self::assertSame('image/jpeg', $map->getMimeType('.JPG'));
        self::assertSame('xml', $map->getExtension('text/xml'));
        self::assertSame('application/xml', $map->getMimeType('xml'));
        self::assertContains('text/xml', $map->getMimeTypes('xml'));
        self::assertContains('264', $map->getAllExtensions());
        foreach ($map->getAllExtensions() as $extension) {
            self::assertIsString($extension);
            self::assertTrue($map->hasExtension($extension));
            self::assertSame($map->getMimeTypes($extension)[0], $map->getMimeType($extension));
        }
        foreach ($map->getAllMimeTypes() as $mime) {
            self::assertTrue(MimeType::isValid($mime), $mime);
            foreach ($map->getExtensions($mime) as $ext) {
                self::assertSame($ext, (new Ext($ext))->value);
                self::assertContains($mime, $map->getMimeTypes($ext));
            }
        }
    }

    public function testNormalizesMutationsAndPreservesOrder(): void
    {
        $map = new MimeMap();
        $map->setMap(['TEXT/X-TEST' => ['.TEST', 'test', 'other'], 'text/x-alt' => ['test']]);
        self::assertSame(['test', 'other'], $map->getExtensions('text/x-test; charset=UTF-8'));
        self::assertSame(['text/x-test', 'text/x-alt'], $map->getMimeTypes('.TEST'));
        self::assertSame('text/x-test', $map->getMimeType('test'));
        $map->extend(['text/x-test' => ['NEW']]);
        self::assertSame(['test', 'other', 'new'], $map->getExtensions('text/x-test'));
        $map->extend(['text/x-test' => ['replacement']], override: true);
        self::assertSame('text/x-alt', $map->getMimeType('test'));
        $map->remove('TEXT/X-ALT');
        self::assertNull($map->getMimeType('test'));
        self::assertSame(['replacement'], $map->getAllExtensions());
    }

    public function testInvalidMutationsAreAtomicWithColdAndWarmIndexes(): void
    {
        foreach ([false, true] as $warm) {
            foreach (['extend', 'setMap'] as $method) {
                $map = (new MimeMap())->setMap(['text/x-original' => ['orig']]);
                if ($warm) {
                    self::assertSame('text/x-original', $map->getMimeType('orig'));
                }
                try {
                    $map->$method(['text/x-new' => ['new'], 'invalid' => ['bad']]);
                    self::fail('Expected invalid MIME type');
                } catch (\InvalidArgumentException) {
                    self::assertSame(['text/x-original'], $map->getAllMimeTypes());
                    self::assertSame(['orig'], $map->getAllExtensions());
                    self::assertNull($map->getMimeType('new'));
                    self::assertSame('text/x-original', $map->getMimeType('orig'));
                }
            }
        }
    }

    public function testRejectsNonListAndInvalidExtensions(): void
    {
        foreach ([['primary' => 'foo'], [42], ['../foo']] as $extensions) {
            try {
                (new MimeMap())->extend(['text/x-test' => $extensions]);
                self::fail('Expected invalid map');
            } catch (\InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }
}
