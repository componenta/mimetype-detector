<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\Ext;
use PHPUnit\Framework\Assert;

test('normalizes and compares extensions', function (): void {
    $extension = new Ext('.JPG');
    Assert::assertSame('jpg', $extension->value);
    Assert::assertSame('jpg', (string) $extension);
    Assert::assertSame('.jpg', $extension->withDot());
    Assert::assertTrue($extension->equals('jpg', '.jpeg'));
    Assert::assertFalse($extension->equals('jpeg'));
    Assert::assertSame('tar.gz', (new Ext('.TAR.GZ'))->value);
    Assert::assertSame('264', (new Ext('264'))->value);
});

test('rejects invalid extensions', function (): void {
    foreach (['', '.', '..jpg', '../jpg', 'a/b', 'a\\b', 'jpg ', "a\0b", 'a..b'] as $value) {
        Assert::assertNull(Ext::tryCreate($value));
    }
    $this->expectException(\InvalidArgumentException::class);
    new Ext('../jpg');
});
