<?php

declare(strict_types=1);

namespace Componenta\Detector\Internal;

use Componenta\Detector\Exception\InvalidMimeTypeException;

/** @internal Parses concrete media types, not Accept media ranges. */
final class MediaTypeParser
{
    private const NAME = '[a-zA-Z0-9][a-zA-Z0-9!#$&^_.+-]{0,126}';
    private const TOKEN = '[!#$%&\'*+.^_\x60|~0-9a-zA-Z-]+';

    /**
     * @return array{string, string, array<array-key, string>}
     * @throws InvalidMimeTypeException
     */
    public static function parse(string $input): array
    {
        // Do not trim CR/LF or other prohibited control bytes away.
        if (self::hasForbiddenControls($input)) {
            throw InvalidMimeTypeException::forValue($input);
        }

        $value = trim($input, " \t");
        if (preg_match('~^(' . self::NAME . ')/(' . self::NAME . ')(?=[ \t;]|$)~D', $value, $parts) !== 1) {
            throw InvalidMimeTypeException::forValue($input);
        }

        $type = strtolower($parts[1]);
        $subtype = strtolower($parts[2]);
        $offset = strlen($parts[0]);
        $length = strlen($value);
        $parameters = [];

        while ($offset < $length) {
            self::skipWhitespace($value, $offset);
            if ($offset === $length) {
                break;
            }
            if ($value[$offset++] !== ';') {
                throw InvalidMimeTypeException::forValue($input);
            }
            self::skipWhitespace($value, $offset);
            // HTTP permits empty parameter segments.
            if ($offset === $length || $value[$offset] === ';') {
                continue;
            }
            if (preg_match('/\G(' . self::TOKEN . ')=/', $value, $name, 0, $offset) !== 1) {
                throw InvalidMimeTypeException::forValue($input);
            }
            $offset += strlen($name[0]);
            $key = strtolower($name[1]);
            // PHP coerces numeric keys to integers; parameter names remain retrievable by name.
            if (array_key_exists($key, $parameters) || $offset === $length) {
                throw InvalidMimeTypeException::forValue($input);
            }

            if ($value[$offset] === '"') {
                $offset++;
                $parameter = '';
                $closed = false;
                while ($offset < $length) {
                    $byte = $value[$offset++];
                    if ($byte === '"') {
                        $closed = true;
                        break;
                    }
                    if ($byte === '\\') {
                        if ($offset === $length) {
                            break;
                        }
                        $byte = $value[$offset++];
                    }
                    $parameter .= $byte;
                }
                if (!$closed) {
                    throw InvalidMimeTypeException::forValue($input);
                }
            } else {
                if (preg_match('/\G' . self::TOKEN . '/', $value, $token, 0, $offset) !== 1) {
                    throw InvalidMimeTypeException::forValue($input);
                }
                $parameter = $token[0];
                $offset += strlen($parameter);
            }

            $parameters[$key] = $parameter;
        }

        return [$type, $subtype, $parameters];
    }

    public static function tryNormalize(string $input): ?string
    {
        try {
            [$type, $subtype] = self::parse($input);
            return $type . '/' . $subtype;
        } catch (InvalidMimeTypeException) {
            return null;
        }
    }

    public static function isParameterName(string $name): bool
    {
        return preg_match('/\A' . self::TOKEN . '\z/D', $name) === 1;
    }

    public static function hasForbiddenControls(string $value): bool
    {
        return preg_match('/[\x00-\x08\x0A-\x1F\x7F]/', $value) !== 0;
    }

    private static function skipWhitespace(string $value, int &$offset): void
    {
        $offset += strspn($value, " \t", $offset);
    }
}
