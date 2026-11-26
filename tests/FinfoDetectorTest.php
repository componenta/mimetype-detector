<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\{DetectorException, Ext, FileNotFoundException, FileNotReadableException, FinfoDetector, MimeMap, MimeType};
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

final class FinfoDetectorTest extends TestCase
{
    public function testDetectsTypedValuesAndUsesMapAsAuthoritativeSource(): void
    {
        $map = (new MimeMap())->setMap(['text/plain' => ['custom']]);
        $detector = new FinfoDetector($map);
        self::assertSame('text/plain', $detector->detectMimeType('plain text')?->value);
        self::assertSame('custom', $detector->detectExtension('plain text')?->value);
        $map->remove('text/plain');
        self::assertNull($detector->detectExtension('plain text'));
        self::assertNull($detector->detectMimeType(''));
        self::assertNull($detector->detectExtension(''));
    }

    public function testPrefixLimitIsTheSameForFileAndString(): void
    {
        $value = '{"data":"' . str_repeat('a', 20000) . '"}';
        $path = tempnam(sys_get_temp_dir(), 'mime');
        self::assertIsString($path);
        try {
            file_put_contents($path, $value);
            $detector = new FinfoDetector(maxBytes: 8192);
            self::assertSame($detector->detectMimeType($value)?->value, $detector->detectFileMimeType($path)?->value);
            self::assertSame($detector->detectExtension($value)?->value, $detector->detectFileExtension($path)?->value);
            self::assertSame('application/json', (new FinfoDetector(maxBytes: 65536))->detectMimeType($value)?->value);
        } finally {
            unlink($path);
        }
    }

    public function testSeekableStreamReadsShortChunksAndRestoresPosition(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(3);
        $stream->expects(self::once())->method('rewind');
        $stream->expects(self::once())->method('seek')->with(3);
        $stream->method('eof')->willReturnOnConsecutiveCalls(false, false, true);
        $stream->expects(self::exactly(2))->method('read')->willReturnOnConsecutiveCalls('plain ', 'text');
        self::assertSame('text/plain', (new FinfoDetector())->detectMimeType($stream)?->value);
    }

    public function testStreamReadFailurePreservesCauseAndPosition(): void
    {
        $cause = new \RuntimeException('read error');
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(7);
        $stream->method('eof')->willReturn(false);
        $stream->method('read')->willThrowException($cause);
        $stream->expects(self::once())->method('seek')->with(7);
        try {
            (new FinfoDetector())->detectMimeType($stream);
            self::fail('Expected detector exception');
        } catch (DetectorException $exception) {
            self::assertSame($cause, $exception->getPrevious());
        }
    }

    public function testRewindFailureStillAttemptsRestore(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(7);
        $stream->method('rewind')->willThrowException(new \RuntimeException('rewind error'));
        $stream->expects(self::once())->method('seek')->with(7);
        $this->expectException(DetectorException::class);
        (new FinfoDetector())->detectMimeType($stream);
    }

    public function testNonSeekableStreamIsConsumedAndReadIsBounded(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(false);
        $stream->expects(self::never())->method('seek');
        $stream->expects(self::never())->method('rewind');
        $stream->method('eof')->willReturn(false);
        $stream->expects(self::once())->method('read')->with(10)->willReturn('plain text');
        self::assertSame('text/plain', (new FinfoDetector(maxBytes: 10))->detectMimeType($stream)?->value);
    }

    public function testEmptyReadBeforeEofFailsWithoutSpinning(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(false);
        $stream->method('eof')->willReturn(false);
        $stream->expects(self::once())->method('read')->willReturn('');
        $this->expectException(DetectorException::class);
        (new FinfoDetector())->detectMimeType($stream);
    }

    public function testRejectsMissingFile(): void
    {
        $this->expectException(FileNotFoundException::class);
        (new FinfoDetector())->detectFileMimeType(__DIR__ . '/missing.txt');
    }

    public function testRejectsDirectoriesAndWrappers(): void
    {
        foreach ([__DIR__, 'data://text/plain,hello', "bad\0path"] as $path) {
            try {
                (new FinfoDetector())->detectFileMimeType($path);
                self::fail('Expected non-regular path rejection');
            } catch (FileNotReadableException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsNonPositiveLimit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FinfoDetector(maxBytes: 0);
    }
}
