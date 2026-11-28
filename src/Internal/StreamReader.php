<?php

declare(strict_types=1);

namespace Componenta\Detector\Internal;

use Componenta\Detector\Exception\StreamExceptionInterface;
use Componenta\Detector\Exception\StreamPositionRestoreException;
use Componenta\Detector\Exception\StreamReadException;
use Psr\Http\Message\StreamInterface;

/** @internal */
final class StreamReader
{
    private const int READ_CHUNK_BYTES = 65536;

    /** @throws StreamExceptionInterface */
    public static function readPrefix(StreamInterface $stream, int $maxBytes): string
    {
        $position = null;
        $failure = null;

        try {
            if (!$stream->isReadable()) {
                throw new StreamReadException('Stream is not readable.');
            }
            if ($stream->isSeekable()) {
                $position = $stream->tell();
                $stream->rewind();
            }

            $content = '';
            $remaining = $maxBytes;
            $madeProgress = true;
            while ($remaining > 0 && !$stream->eof()) {
                if (!$madeProgress) {
                    throw new StreamReadException('Stream read made no progress before EOF.');
                }

                $length = min(self::READ_CHUNK_BYTES, $remaining);
                $chunk = $stream->read($length);
                $bytes = strlen($chunk);
                if ($bytes > $length) {
                    throw new StreamReadException('Stream returned more bytes than requested.');
                }
                $madeProgress = $bytes !== 0;
                $content .= $chunk;
                $remaining -= $bytes;
            }

            return $content;
        } catch (\Throwable $error) {
            $failure = $error instanceof \RuntimeException && !$error instanceof StreamExceptionInterface
                ? new StreamReadException('Unable to read stream content.', previous: $error)
                : $error;
            throw $failure;
        } finally {
            if ($position !== null) {
                try {
                    $stream->seek($position);
                } catch (\Throwable $error) {
                    throw new StreamPositionRestoreException($error, $failure);
                }
            }
        }
    }
}
