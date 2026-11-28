<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Detector\{FinfoDetector, MimeMap, MimeMapInterface};
use Componenta\Detector\Exception\{DetectorConfigurationException, DetectorException, DetectorExceptionInterface, FileExceptionInterface, FileReadException, FileUnavailableException, InvalidFileException, StreamExceptionInterface, StreamPositionRestoreException, StreamPositionRestoreExceptionInterface, StreamReadException};
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
    } catch (StreamReadException $exception) {
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
    $this->expectException(StreamReadException::class);
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
    $this->expectException(StreamReadException::class);
    (new FinfoDetector())->detectMimeType($stream);
});

test('rejects missing file', function (): void {
    $this->expectException(FileUnavailableException::class);
    (new FinfoDetector())->detectFileMimeType(__DIR__ . '/missing.txt');
});

test('rejects directories and wrappers', function (): void {
    foreach ([__DIR__, '', 'file://' . __FILE__, 'data://text/plain,hello', 'data:text/plain,hello', "bad\0path"] as $path) {
        try {
            (new FinfoDetector())->detectFileMimeType($path);
            Assert::fail('Expected non-regular path rejection');
        } catch (InvalidFileException) {
            Assert::assertTrue(true);
        }
    }
});

test('rejects non positive limit', function (): void {
    $this->expectException(DetectorConfigurationException::class);
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
    } catch (StreamReadException) {
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
    } catch (StreamReadException $exception) {
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
        } catch (StreamPositionRestoreExceptionInterface $exception) {
            Assert::assertInstanceOf(StreamPositionRestoreException::class, $exception);
            Assert::assertSame($restore, $exception->getPrevious());
            if ($readFails) {
                Assert::assertInstanceOf(StreamReadException::class, $exception->readFailure);
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
        $this->expectException(InvalidFileException::class);
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
    $this->expectException(StreamReadException::class);
    (new FinfoDetector(maxBytes: 4))->detectMimeType($stream);
});

test('propagates undeclared map failures without affecting MIME detection', function (): void {
    $cause = new \RuntimeException('custom map unavailable');
    $map = $this->createMock(MimeMapInterface::class);
    $map->expects(TestCase::once())->method('getExtension')->with('text/plain')->willThrowException($cause);
    $detector = new FinfoDetector($map);
    Assert::assertSame('text/plain', $detector->detectMimeType('plain text')?->value);
    try {
        $detector->detectExtension('plain text');
        Assert::fail('Expected the original map failure.');
    } catch (\RuntimeException $exception) {
        Assert::assertSame($cause, $exception);
    }
});

test('rejects malformed extension values from a custom map', function (): void {
    $map = $this->createMock(MimeMapInterface::class);
    $map->method('getExtension')->willReturn('../invalid');
    $this->expectException(DetectorException::class);
    (new FinfoDetector($map))->detectExtension('plain text');
});

test('uses the general PostScript suffix instead of a specific application suffix', function (): void {
    $bytes = "%!PS-Adobe-3.0\n%%Pages: 1\nshowpage\n";
    $detector = new FinfoDetector();
    Assert::assertSame('application/postscript', $detector->detectMimeType($bytes)?->value);
    Assert::assertSame('ps', $detector->detectExtension($bytes)?->value);
});

test('rejects a regular file replaced with a FIFO before open without blocking', function (): void {
    if (PHP_OS_FAMILY === 'Windows' || !function_exists('posix_mkfifo') || !function_exists('pcntl_alarm') || !function_exists('proc_open')) {
        $this->markTestSkipped('POSIX FIFO and subprocess support required.');
    }

    $directory = sys_get_temp_dir() . '/mime-replace-' . bin2hex(random_bytes(8));
    Assert::assertTrue(mkdir($directory, 0700));
    $path = $directory . '/file';
    $fifo = $directory . '/pipe';
    try {
        Assert::assertSame(10, file_put_contents($path, 'plain text'));
        Assert::assertTrue(posix_mkfifo($fifo, 0600));

        $process = proc_open(
            [PHP_BINARY, __DIR__ . '/fixtures/replace-with-fifo.php', $path, $fifo],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );
        Assert::assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        Assert::assertSame(0, proc_close($process), (string) $errors);
        Assert::assertSame("Rejected substituted FIFO.\n", $output);
    } finally {
        foreach ([$path, $fifo] as $entry) {
            if (file_exists($entry)) {
                unlink($entry);
            }
        }
        rmdir($directory);
    }
});

test('accepts an empty terminal read when EOF becomes true', function (): void {
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(false);
    $stream->method('eof')->willReturnOnConsecutiveCalls(false, false, true);
    $stream->expects(TestCase::exactly(2))->method('read')
        ->willReturnOnConsecutiveCalls('plain text', '');

    Assert::assertSame('text/plain', (new FinfoDetector())->detectMimeType($stream)?->value);
});


test('file and stream failures are independent of detection failures', function (): void {
    $detector = new FinfoDetector();
    try {
        $detector->detectFileMimeType(__DIR__ . '/missing.txt');
        Assert::fail('Expected file acquisition failure.');
    } catch (FileExceptionInterface $error) {
        Assert::assertInstanceOf(FileUnavailableException::class, $error);
        Assert::assertNotInstanceOf(DetectorExceptionInterface::class, $error);
        Assert::assertNotInstanceOf(StreamExceptionInterface::class, $error);
    }
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(false);
    try {
        $detector->detectMimeType($stream);
        Assert::fail('Expected stream acquisition failure.');
    } catch (StreamExceptionInterface $error) {
        Assert::assertInstanceOf(StreamReadException::class, $error);
        Assert::assertNotInstanceOf(DetectorExceptionInterface::class, $error);
        Assert::assertNotInstanceOf(FileExceptionInterface::class, $error);
    }

    $map = $this->createMock(MimeMapInterface::class);
    $cause = new DetectorException('Mapping backend failed.');
    $map->method('getExtension')->willThrowException($cause);
    try {
        (new FinfoDetector($map))->detectExtension('plain text');
        Assert::fail('Expected detection failure.');
    } catch (DetectorExceptionInterface $error) {
        Assert::assertSame($cause, $error);
        Assert::assertNotInstanceOf(FileExceptionInterface::class, $error);
        Assert::assertNotInstanceOf(StreamExceptionInterface::class, $error);
    }
});

test('unexpected stream defects propagate unchanged when restoration succeeds', function (): void {
    $error = new \LogicException('Implementation defect.');
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(true);
    $stream->method('tell')->willReturn(4);
    $stream->method('eof')->willReturn(false);
    $stream->method('read')->willThrowException($error);
    $stream->expects(TestCase::once())->method('seek')->with(4);
    try {
        (new FinfoDetector())->detectMimeType($stream);
        Assert::fail('Expected original programming error.');
    } catch (\LogicException $caught) {
        Assert::assertSame($error, $caught);
    }
});

test('restoration failure preserves an unexpected original error', function (): void {
    $read = new \LogicException('Implementation defect.');
    $restore = new \RuntimeException('Restoration failure.');
    $stream = $this->createMock(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(true);
    $stream->method('tell')->willReturn(4);
    $stream->method('eof')->willReturn(false);
    $stream->method('read')->willThrowException($read);
    $stream->expects(TestCase::once())->method('seek')->with(4)->willThrowException($restore);
    try {
        (new FinfoDetector())->detectMimeType($stream);
        Assert::fail('Expected restoration failure.');
    } catch (StreamPositionRestoreExceptionInterface $error) {
        Assert::assertSame($restore, $error->getPrevious());
        Assert::assertSame($read, $error->readFailure);
        Assert::assertNotInstanceOf(DetectorExceptionInterface::class, $error);
    }
});

test('unexpected map programming errors are not converted into detection errors', function (): void {
    $cause = new \LogicException('Map implementation defect.');
    $map = $this->createMock(MimeMapInterface::class);
    $map->method('getExtension')->willThrowException($cause);
    try {
        (new FinfoDetector($map))->detectExtension('plain text');
        Assert::fail('Expected original error.');
    } catch (\LogicException $error) {
        Assert::assertSame($cause, $error);
    }
});

test('file errors do not escape to the application warning handler and the handler is restored', function (): void {
    $handler = static function (): never {
        throw new \LogicException('Unexpected application warning.');
    };
    set_error_handler($handler);
    try {
        try {
            (new FinfoDetector())->detectFileMimeType(__DIR__ . '/missing.txt');
            Assert::fail('Expected unavailable file.');
        } catch (FileUnavailableException) {
            Assert::assertTrue(true);
        }
        $previous = set_error_handler(static fn(): bool => false);
        restore_error_handler();
        Assert::assertSame($handler, $previous);
    } finally {
        restore_error_handler();
    }
});

test('file detection accepts a symlink to a regular file without modifying its contents', function (): void {
    if (!function_exists('symlink')) {
        $this->markTestSkipped('Symlinks are unavailable.');
    }
    $path = tempnam(sys_get_temp_dir(), 'mime-link');
    Assert::assertIsString($path);
    $link = $path . '.link';
    try {
        file_put_contents($path, 'plain text');
        if (!@symlink($path, $link)) {
            $this->markTestSkipped('Cannot create symbolic link.');
        }
        Assert::assertSame('text/plain', (new FinfoDetector())->detectFileMimeType($link)?->value);
        Assert::assertSame('plain text', file_get_contents($path));
    } finally {
        if (is_link($link)) {
            unlink($link);
        }
        unlink($path);
    }
});


test('file reader preserves both a read error and a failed close', function (): void {
    if (!function_exists('proc_open')) {
        $this->markTestSkipped('Subprocess support is unavailable.');
    }

    $path = tempnam(sys_get_temp_dir(), 'mime-cleanup');
    Assert::assertIsString($path);
    try {
        file_put_contents($path, 'plain text');
        $process = proc_open(
            [PHP_BINARY, __DIR__ . '/fixtures/file-cleanup-failure.php', $path],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );
        Assert::assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        Assert::assertSame(0, proc_close($process), (string) $errors);
        Assert::assertSame("Both failures preserved; handler restored.\n", $output);
    } finally {
        unlink($path);
    }
});

test('a real unreadable regular file is classified as a file acquisition failure', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'mime-permission');
    Assert::assertIsString($path);
    try {
        file_put_contents($path, 'plain text');
        chmod($path, 0000);
        clearstatcache(true, $path);
        if (is_readable($path)) {
            $this->markTestSkipped('Current privileges permit reading the file.');
        }
        try {
            (new FinfoDetector())->detectFileMimeType($path);
            Assert::fail('Expected a file acquisition failure.');
        } catch (FileExceptionInterface $error) {
            Assert::assertTrue($error instanceof FileReadException || $error instanceof FileUnavailableException);
            Assert::assertNotInstanceOf(DetectorExceptionInterface::class, $error);
        }
    } finally {
        chmod($path, 0600);
        unlink($path);
    }
});

test('native initialization failure is a configuration failure and restores the warning handler', function (): void {
    if (!function_exists('proc_open') || !function_exists('putenv')) {
        $this->markTestSkipped('Subprocess and environment support required.');
    }

    $process = proc_open(
        [PHP_BINARY, __DIR__ . '/fixtures/finfo-initialization-failure.php'],
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
    );
    Assert::assertIsResource($process);
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    Assert::assertSame(0, proc_close($process), (string) $errors);
    Assert::assertSame("Configuration failure preserved; handler restored.\n", $output);
});

test('unexpected stream state-query failures propagate unchanged', function (string $method): void {
    $error = new \RuntimeException('Unexpected state query failure.');
    $stream = $this->createMock(StreamInterface::class);
    if ($method !== 'isReadable') {
        $stream->method('isReadable')->willReturn(true);
    }
    if ($method === 'eof') {
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(4);
        $stream->expects(TestCase::once())->method('seek')->with(4);
    } else {
        $stream->expects(TestCase::never())->method('seek');
    }
    $stream->method($method)->willThrowException($error);
    $stream->expects(TestCase::never())->method('read');

    try {
        (new FinfoDetector())->detectMimeType($stream);
        Assert::fail('Expected the original stream state-query failure.');
    } catch (\RuntimeException $caught) {
        Assert::assertSame($error, $caught);
    }
})->with(['isReadable', 'isSeekable', 'eof']);

test('a declared stream read failure is not wrapped again', function (): void {
    $error = new StreamReadException('Already classified.');
    $stream = $this->createStub(StreamInterface::class);
    $stream->method('isReadable')->willReturn(true);
    $stream->method('isSeekable')->willReturn(false);
    $stream->method('eof')->willReturn(false);
    $stream->method('read')->willThrowException($error);

    try {
        (new FinfoDetector())->detectMimeType($stream);
        Assert::fail('Expected the original stream failure.');
    } catch (StreamReadException $caught) {
        Assert::assertSame($error, $caught);
    }
});
