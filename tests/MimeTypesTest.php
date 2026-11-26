<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\MimeTypes\{Application, Audio, Font, Image, Message, Model, Multipart, Text, Video};
use PHPUnit\Framework\Assert;

test('every enum has unique canonical values and round trips', function (): void {
    foreach ([Application::class, Audio::class, Font::class, Image::class, Message::class, Model::class, Multipart::class, Text::class, Video::class] as $enum) {
        $values = [];
        foreach ($enum::cases() as $case) {
            Assert::assertSame(strtolower($case->value), $case->value);
            Assert::assertArrayNotHasKey($case->value, $values);
            Assert::assertSame($case, $enum::from($case->value));
            Assert::assertSame($case, $enum::tryFrom($case->value));
            $values[$case->value] = true;
        }
    }
});

test('aliases refer to canonical cases', function (): void {
    Assert::assertSame(Application::Pkcs12, Application::XPkcs12);
    Assert::assertSame(Audio::XApe, Audio::XMonkeysAudio);
    Assert::assertSame(Audio::XTta, Audio::Tta);
    Assert::assertSame(Video::Asf, Video::MsAsf);
    Assert::assertSame(Text::VndIptc, Text::VndIptcAnpa);
});

test('classifies concrete media type names without depending on value objects', function (): void {
    Assert::assertTrue(Image::isImage('IMAGE/PNG; x=1'));
    Assert::assertFalse(Image::isImage('image/*'));
    Assert::assertFalse(Image::isImage('image/'));
    Assert::assertTrue(Audio::isAudio('Audio/L16'));
    Assert::assertTrue(Video::isVideo('video/H264'));
    Assert::assertTrue(Font::isFont('application/x-font-ttf'));
    Assert::assertFalse(Font::isFont('application/not-a-font'));
    Assert::assertFalse(Application::isApplication('application/invalid; x='));
    Assert::assertTrue(Text::isText('text/plain'));
    Assert::assertTrue(Message::isMessage('message/CPIM'));
    Assert::assertTrue(Model::isModel('model/JT'));
    Assert::assertTrue(Multipart::isMultipart('multipart/form-data; boundary=abc'));
});

test('recognizes legacy font and text labels observed in fileinfo', function (): void {
    Assert::assertTrue(Font::isFont('application/vnd.ms-opentype'));
    Assert::assertSame(Font::VndMsOpentype, Font::from('application/vnd.ms-opentype'));
    Assert::assertSame(Text::Rtf, Text::from('text/rtf'));
    Assert::assertSame(Text::XScriptPython, Text::from('text/x-script.python'));
});
