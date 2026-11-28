<?php

declare(strict_types=1);

namespace Componenta\Detector\Internal;

use Componenta\Detector\Exception\FileExceptionInterface;
use Componenta\Detector\Exception\FileReadException;
use Componenta\Detector\Exception\FileUnavailableException;
use Componenta\Detector\Exception\InvalidFileException;
use Componenta\Stdlib\Exception\FileExceptionInterface as ReaderExceptionInterface;
use Componenta\Stdlib\Exception\FileReadException as ReaderReadException;
use Componenta\Stdlib\Exception\FileUnavailableException as ReaderUnavailableException;
use Componenta\Stdlib\Exception\InvalidFileException as ReaderInvalidException;
use Componenta\Stdlib\FileReader as LocalFileReader;

/** @internal Translates shared file acquisition failures to the detector's public contract. */
final class FileReader
{
    /** @throws FileExceptionInterface */
    public static function readPrefix(string $path, int $maxBytes): string
    {
        try {
            return LocalFileReader::readPrefix($path, $maxBytes);
        } catch (ReaderInvalidException $error) {
            throw new InvalidFileException($error->getMessage(), previous: $error);
        } catch (ReaderUnavailableException $error) {
            throw new FileUnavailableException($error->getMessage(), previous: $error->getPrevious() ?? $error);
        } catch (ReaderReadException $error) {
            throw new FileReadException(
                $error->getMessage(),
                previous: $error->getPrevious() ?? $error,
                readFailure: $error->readFailure,
            );
        } catch (ReaderExceptionInterface $error) {
            throw new FileReadException($error->getMessage(), previous: $error);
        }
    }
}
