<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\{DetectorException, Ext, FileNotFoundException, FileNotReadableException, FinfoDetector, MimeMap, MimeType};
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

test('detects typed values and uses map as authoritative source', function (): void {
    $map = (new MimeMap())->setMap(['text/plain' => ['custom']]);
    $detector = new FinfoDetector($map);
    Assert::assertSame('text/plain', $detector->detectMimeType('plain text')?->value);
    Assert::assertSame('custom', $detector->detectExtension('plain text')?->value);
    $map->remove('text/plain');
    Assert::assertNull($detector->detectExtension('plain text'));
    Assert::assertNull($detector->detectMimeType(''));
    Assert::assertNull($detector->detectExtension(''));
});

test('prefix limit is the same for file and string', function (): void {
    $value = '{"data":"' . str_repeat('a', 20000) . '"}';
    $path = tempnam(sys_get_temp_dir(), 'mime');
    Assert::assertIsString($path);
    try {
        file_put_contents($path, $value);
        $detector = new FinfoDetector(maxBytes: 8192);
        Assert::assertSame($detector->detectMimeType($value)?->value, $detector->detectFileMimeType($path)?->value);
        Assert::assertSame($detector->detectExtension($value)?->value, $detector->detectFileExtension($path)?->value);
        Assert::assertSame('application/json', (new FinfoDetector(maxBytes: 65536))->detectMimeType($value)?->value);
    } finally {
        unlink($path);
    }
});

test('seekable stream reads short chunks and restores position', function (): void {
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(true);
    $stream->method('tell')->willReturn(3);
    $stream->expects(TestCase::once())->method('rewind');
    $stream->expects(TestCase::once())->method('seek')->with(3);
    $stream->method('eof')->willReturnOnConsecutiveCalls(false, false, true);
    $stream->expects(TestCase::exactly(2))->method('read')->willReturnOnConsecutiveCalls('plain ', 'text');
    Assert::assertSame('text/plain', (new FinfoDetector())->detectMimeType($stream)?->value);
});

test('stream read failure preserves cause and position', function (): void {
    $cause = new \RuntimeException('read error');
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(true);
    $stream->method('tell')->willReturn(7);
    $stream->method('eof')->willReturn(false);
    $stream->method('read')->willThrowException($cause);
    $stream->expects(TestCase::once())->method('seek')->with(7);
    try {
        (new FinfoDetector())->detectMimeType($stream);
        Assert::fail('Expected detector exception');
    } catch (DetectorException $exception) {
        Assert::assertSame($cause, $exception->getPrevious());
    }
});

test('rewind failure still attempts restore', function (): void {
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(true);
    $stream->method('tell')->willReturn(7);
    $stream->method('rewind')->willThrowException(new \RuntimeException('rewind error'));
    $stream->expects(TestCase::once())->method('seek')->with(7);
    $this->expectException(DetectorException::class);
    (new FinfoDetector())->detectMimeType($stream);
});

test('non seekable stream is consumed and read is bounded', function (): void {
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(false);
    $stream->expects(TestCase::never())->method('seek');
    $stream->expects(TestCase::never())->method('rewind');
    $stream->method('eof')->willReturn(false);
    $stream->expects(TestCase::once())->method('read')->with(10)->willReturn('plain text');
    Assert::assertSame('text/plain', (new FinfoDetector(maxBytes: 10))->detectMimeType($stream)?->value);
});

test('empty read before eof fails without spinning', function (): void {
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(false);
    $stream->method('eof')->willReturn(false);
    $stream->expects(TestCase::once())->method('read')->willReturn('');
    $this->expectException(DetectorException::class);
    (new FinfoDetector())->detectMimeType($stream);
});

test('rejects missing file', function (): void {
    $this->expectException(FileNotFoundException::class);
    (new FinfoDetector())->detectFileMimeType(__DIR__ . '/missing.txt');
});

test('rejects directories and wrappers', function (): void {
    foreach ([__DIR__, 'data://text/plain,hello', "bad\0path"] as $path) {
        try {
            (new FinfoDetector())->detectFileMimeType($path);
            Assert::fail('Expected non-regular path rejection');
        } catch (FileNotReadableException) {
            Assert::assertTrue(true);
        }
    }
});

test('rejects non positive limit', function (): void {
    $this->expectException(\InvalidArgumentException::class);
    new FinfoDetector(maxBytes: 0);
});
