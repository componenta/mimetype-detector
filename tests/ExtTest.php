<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\Ext;
use PHPUnit\Framework\TestCase;

final class ExtTest extends TestCase
{
    public function testNormalizesExtension(): void
    {
        $extension = new Ext('.JPG');

        self::assertSame('jpg', $extension->value);
        self::assertSame('jpg', (string) $extension);
        self::assertSame('.jpg', $extension->withDot());
    }

    public function testSupportsCategoryHelpers(): void
    {
        $extension = new Ext('jpg');

        self::assertTrue($extension->equals('jpg', '.jpeg'));
        self::assertTrue($extension->isImage());
        self::assertFalse($extension->isArchive());
    }
}
