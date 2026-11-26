<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\{DetectorException, FileNotFoundException, FileNotReadableException, FinfoDetector, MimeMap, StreamPositionRestoreException};
use GuzzleHttp\Psr7\Utils;
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

test('classifies representative formats consistently across all input sources', function (): void {
    $fixtures = [
        ['image/gif', 'gif', base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', true)],
        ['image/png', 'png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/S9sAAAAASUVORK5CYII=', true)],
        ['application/pdf', 'pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n"],
        ['application/json', 'json', '{"name":"value"}'],
        ['text/plain', 'txt', 'plain text'],
    ];
    $detector = new FinfoDetector();
    $path = tempnam(sys_get_temp_dir(), 'mime');
    Assert::assertIsString($path);
    try {
        foreach ($fixtures as [$type, $extension, $bytes]) {
            Assert::assertIsString($bytes);
            file_put_contents($path, $bytes);
            $stream = Utils::streamFor($bytes);
            $stream->seek(1);
            Assert::assertSame($type, $detector->detectMimeType($bytes)?->value);
            Assert::assertSame($type, $detector->detectFileMimeType($path)?->value);
            Assert::assertSame($type, $detector->detectMimeType($stream)?->value);
            Assert::assertSame(1, $stream->tell());
            Assert::assertSame($extension, $detector->detectExtension($bytes)?->value);
            Assert::assertSame($extension, $detector->detectFileExtension($path)?->value);
            Assert::assertSame($extension, $detector->detectExtension($stream)?->value);
            Assert::assertSame(1, $stream->tell());
            $stream->close();
        }
        file_put_contents($path, '');
        Assert::assertNull($detector->detectFileMimeType($path));
        Assert::assertNull($detector->detectFileExtension($path));
    } finally {
        unlink($path);
    }
});

test('rejects a non-readable stream and wraps tell failures', function (): void {
    $detector = new FinfoDetector();
    $stream = Utils::streamFor('plain text');
    $stream->close();
    try {
        $detector->detectMimeType($stream);
        Assert::fail('Expected unreadable stream failure');
    } catch (DetectorException) {
        Assert::assertTrue(true);
    }

    $cause = new \RuntimeException('tell failed');
    $mock = $this->createMock(StreamInterface::class);
    $mock->method('isReadable')->willReturn(true);
    $mock->method('isSeekable')->willReturn(true);
    $mock->method('tell')->willThrowException($cause);
    $mock->expects(TestCase::never())->method('rewind');
    $mock->expects(TestCase::never())->method('seek');
    try {
        $detector->detectMimeType($mock);
        Assert::fail('Expected tell failure');
    } catch (DetectorException $exception) {
        Assert::assertSame($cause, $exception->getPrevious());
    }
});

test('reports restoration failure and retains an original read failure', function (): void {
    $read = new \RuntimeException('read failed');
    $restore = new \RuntimeException('seek failed');
    foreach ([false, true] as $readFails) {
        $mock = $this->createMock(StreamInterface::class);
        $mock->method('isReadable')->willReturn(true);
        $mock->method('isSeekable')->willReturn(true);
        $mock->method('tell')->willReturn(2);
        $mock->expects(TestCase::once())->method('seek')->with(2)->willThrowException($restore);
        if ($readFails) {
            $mock->method('eof')->willReturn(false);
            $mock->method('read')->willThrowException($read);
        } else {
            $mock->method('eof')->willReturnOnConsecutiveCalls(false, true);
            $mock->method('read')->willReturn('plain text');
        }
        try {
            (new FinfoDetector())->detectMimeType($mock);
            Assert::fail('Expected restoration failure');
        } catch (DetectorException $exception) {
            Assert::assertInstanceOf(StreamPositionRestoreException::class, $exception);
            Assert::assertSame($restore, $exception->getPrevious());
            if ($readFails) {
                Assert::assertInstanceOf(DetectorException::class, $exception->readFailure);
                Assert::assertSame($read, $exception->readFailure->getPrevious());
            } else {
                Assert::assertNull($exception->readFailure);
            }
        }
    }
});

test('rejects a named pipe without opening it', function (): void {
    if (!function_exists('posix_mkfifo')) {
        $this->markTestSkipped('POSIX FIFO support is unavailable.');
    }
    $path = tempnam(sys_get_temp_dir(), 'mime-fifo');
    Assert::assertIsString($path);
    unlink($path);
    if (!posix_mkfifo($path, 0600)) {
        $this->markTestSkipped('Cannot create FIFO in this environment.');
    }
    try {
        $this->expectException(FileNotReadableException::class);
        (new FinfoDetector())->detectFileMimeType($path);
    } finally {
        unlink($path);
    }
});

test('a large configured limit does not preallocate it for a small file or stream', function (): void {
    $detector = new FinfoDetector(maxBytes: PHP_INT_MAX);
    $path = tempnam(sys_get_temp_dir(), 'mime-limit');
    Assert::assertIsString($path);
    try {
        file_put_contents($path, 'plain text');
        Assert::assertSame('text/plain', $detector->detectFileMimeType($path)?->value);
    } finally {
        unlink($path);
    }

    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(false);
    $stream->method('eof')->willReturnOnConsecutiveCalls(false, true);
    $stream->expects(TestCase::once())->method('read')
        ->with(65536)->willReturn('plain text');
    Assert::assertSame('text/plain', $detector->detectMimeType($stream)?->value);
});

test('rejects a stream that returns more than the requested number of bytes', function (): void {
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(false);
    $stream->method('eof')->willReturn(false);
    $stream->expects(TestCase::once())->method('read')->with(4)->willReturn('12345');
    $this->expectException(DetectorException::class);
    (new FinfoDetector(maxBytes: 4))->detectMimeType($stream);
});
