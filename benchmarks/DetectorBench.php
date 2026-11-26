<?php

declare(strict_types=1);

namespace Componenta\Detector\Benchmarks;

use Componenta\Detector\FinfoDetector;
use Componenta\Detector\MimeMap;
use Componenta\Detector\MimeType;
use Componenta\Detector\Ext;
use GuzzleHttp\Psr7\Utils;
use PhpBench\Attributes as Bench;
use Psr\Http\Message\StreamInterface;

#[Bench\Revs(100)]
#[Bench\Iterations(5)]
final class DetectorBench
{
    private MimeMap $map;
    private FinfoDetector $detector;
    private string $json;
    private string $file;
    private StreamInterface $stream;

    public function setUp(): void
    {
        $this->map = new MimeMap();
        $this->map->getMimeTypes('jpg');
        $this->detector = new FinfoDetector($this->map);
        $this->json = '{"example":"' . str_repeat('a', 1000) . '"}';
        $this->stream = Utils::streamFor($this->json);
        $this->stream->seek(5);
        $file = tempnam(sys_get_temp_dir(), 'mime-bench');
        if ($file === false) {
            throw new \RuntimeException('Cannot create benchmark fixture.');
        }
        $this->file = $file;
        file_put_contents($this->file, $this->json);
    }

    public function tearDown(): void
    {
        if (isset($this->stream)) {
            $this->stream->close();
        }
        if (isset($this->file)) {
            unlink($this->file);
        }
    }

    public function benchParseMediaType(): void
    {
        new MimeType('text/plain; charset=utf-8; name="a;b"');
    }

    public function benchNormalizeExtension(): void
    {
        new Ext('.TAR.GZ');
    }

    public function benchColdMap(): void
    {
        (new MimeMap())->getMimeTypes('jpg');
    }

    #[Bench\BeforeMethods('setUp')]
    #[Bench\AfterMethods('tearDown')]
    public function benchWarmReverseLookup(): void
    {
        $this->map->getMimeTypes('jpg');
    }

    #[Bench\BeforeMethods('setUp')]
    #[Bench\AfterMethods('tearDown')]
    public function benchForwardLookup(): void
    {
        $this->map->getExtension('image/jpeg');
    }

    public function benchConstructDetector(): void
    {
        new FinfoDetector();
    }

    #[Bench\BeforeMethods('setUp')]
    #[Bench\AfterMethods('tearDown')]
    public function benchDetectMime(): void
    {
        $this->detector->detectMimeType($this->json);
    }

    #[Bench\BeforeMethods('setUp')]
    #[Bench\AfterMethods('tearDown')]
    public function benchDetectExtension(): void
    {
        $this->detector->detectExtension($this->json);
    }

    #[Bench\BeforeMethods('setUp')]
    #[Bench\AfterMethods('tearDown')]
    public function benchDetectFile(): void
    {
        $this->detector->detectFileMimeType($this->file);
    }

    #[Bench\BeforeMethods('setUp')]
    #[Bench\AfterMethods('tearDown')]
    public function benchDetectStream(): void
    {
        $this->detector->detectMimeType($this->stream);
    }

    #[Bench\BeforeMethods('setUp')]
    #[Bench\AfterMethods('tearDown')]
    public function benchDetectFileExtension(): void
    {
        $this->detector->detectFileExtension($this->file);
    }

    #[Bench\BeforeMethods('setUp')]
    #[Bench\AfterMethods('tearDown')]
    public function benchDetectStreamExtension(): void
    {
        $this->detector->detectExtension($this->stream);
    }
}
