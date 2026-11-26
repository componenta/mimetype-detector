<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\Ext;
use PHPUnit\Framework\TestCase;

final class ExtTest extends TestCase
{
    public function testNormalizesAndComparesExtensions(): void
    {
        $extension = new Ext('.JPG');
        self::assertSame('jpg', $extension->value);
        self::assertSame('jpg', (string) $extension);
        self::assertSame('.jpg', $extension->withDot());
        self::assertTrue($extension->equals('jpg', '.jpeg'));
        self::assertFalse($extension->equals('jpeg'));
        self::assertSame('tar.gz', (new Ext('.TAR.GZ'))->value);
        self::assertSame('264', (new Ext('264'))->value);
    }

    public function testRejectsInvalidExtensions(): void
    {
        foreach (['', '.', '..jpg', '../jpg', 'a/b', 'a\\b', 'jpg ', "a\0b", 'a..b'] as $value) {
            self::assertNull(Ext::tryCreate($value));
        }
        $this->expectException(\InvalidArgumentException::class);
        new Ext('../jpg');
    }
}
