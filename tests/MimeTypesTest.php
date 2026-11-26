<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\MimeTypes\{Application, Audio, Font, Image, Message, Model, Multipart, Text, Video};
use PHPUnit\Framework\TestCase;

final class MimeTypesTest extends TestCase
{
    public function testEveryEnumHasUniqueCanonicalValuesAndRoundTrips(): void
    {
        foreach ([Application::class, Audio::class, Font::class, Image::class, Message::class, Model::class, Multipart::class, Text::class, Video::class] as $enum) {
            $values = [];
            foreach ($enum::cases() as $case) {
                self::assertSame(strtolower($case->value), $case->value);
                self::assertArrayNotHasKey($case->value, $values);
                self::assertSame($case, $enum::from($case->value));
                self::assertSame($case, $enum::tryFrom($case->value));
                $values[$case->value] = true;
            }
        }
    }

    public function testAliasesReferToCanonicalCases(): void
    {
        self::assertSame(Application::Pkcs12, Application::XPkcs12);
        self::assertSame(Audio::XApe, Audio::XMonkeysAudio);
        self::assertSame(Audio::XTta, Audio::Tta);
        self::assertSame(Video::Asf, Video::MsAsf);
        self::assertSame(Text::VndIptc, Text::VndIptcAnpa);
    }
}
