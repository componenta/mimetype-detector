<?php

declare(strict_types=1);

namespace Componenta\Detector\Internal;

use Componenta\Detector\Exception\StreamExceptionInterface;
use Componenta\Detector\Exception\StreamPositionRestoreException;
use Componenta\Detector\Exception\StreamReadException;
use Componenta\Stdlib\Exception\StreamExceptionInterface as ReaderExceptionInterface;
use Componenta\Stdlib\Exception\StreamPositionRestoreException as ReaderRestoreException;
use Componenta\Stdlib\StreamReader as SharedStreamReader;
use Psr\Http\Message\StreamInterface;

/** @internal Translates shared stream reading failures to the detector contract. */
final class StreamReader
{
    /** @throws StreamExceptionInterface */
    public static function readPrefix(StreamInterface $stream, int $maxBytes): string
    {
        try {
            return SharedStreamReader::readPrefix($stream, $maxBytes);
        } catch (ReaderExceptionInterface $error) {
            throw self::translate($error);
        }
    }

    private static function translate(\Throwable $error): \Throwable
    {
        if ($error instanceof ReaderRestoreException) {
            return new StreamPositionRestoreException(
                $error->getPrevious() ?? $error,
                $error->readFailure === null ? null : self::translate($error->readFailure),
            );
        }
        if (!$error instanceof ReaderExceptionInterface) {
            return $error;
        }
        $previous = $error->getPrevious();
        if ($previous instanceof StreamExceptionInterface) {
            return $previous;
        }
        return new StreamReadException($error->getMessage(), previous: $previous ?? $error);
    }
}
