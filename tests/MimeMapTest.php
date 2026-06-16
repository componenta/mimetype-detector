<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\MimeMap;
use PHPUnit\Framework\TestCase;

final class MimeMapTest extends TestCase
{
    public function testMapsMimeTypesAndExtensions(): void
    {
        $map = new MimeMap();

        self::assertSame('jpg', $map->getExtension('image/jpeg'));
        self::assertContains('jpeg', $map->getExtensions('image/jpeg'));
        self::assertSame('image/jpeg', $map->getMimeType('.jpg'));
        self::assertContains('image/jpeg', $map->getMimeTypes('jpeg'));
        self::assertTrue($map->isValidExtension('image/jpeg', 'jpg'));
        self::assertTrue($map->hasMimeType('image/jpeg'));
        self::assertTrue($map->hasExtension('jpg'));
    }

    public function testCanExtendRemoveAndReplaceMap(): void
    {
        $map = new MimeMap();

        $map->extend(['application/x-custom' => ['cust']]);
        self::assertSame('cust', $map->getExtension('application/x-custom'));
        self::assertSame('application/x-custom', $map->getMimeType('cust'));

        $map->remove('application/x-custom');
        self::assertNull($map->getMimeType('cust'));

        $map->setMap(['text/x-test' => ['test']]);
        self::assertSame(['text/x-test'], $map->getAllMimeTypes());
        self::assertSame(['test'], $map->getAllExtensions());
    }
}
