<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Componenta\Detector\Exception\InvalidMimeTypeException;
use Componenta\Detector\Internal\MediaTypeParser;

/**
 * Immutable, case-insensitive collection of decoded MIME type parameters.
 *
 * @implements \ArrayAccess<mixed, string|null>
 * @implements \IteratorAggregate<string, string>
 */
final readonly class MimeParameters implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /** @var array<array-key, string> */
    private array $parameters;

    /**
     * @param array<array-key, string> $parameters Decoded values, not quoted header syntax.
     * @throws InvalidMimeTypeException Invalid names, values or duplicate normalized names.
     */
    public function __construct(array $parameters = [])
    {
        $normalized = [];
        /** @var array<array-key, mixed> $parameters */
        foreach ($parameters as $name => $value) {
            $name = strtolower((string) $name);
            if (!is_string($value)
                || !MediaTypeParser::isParameterName($name)
                || MediaTypeParser::hasForbiddenControls($value)
                || isset($normalized[$name])) {
                throw InvalidMimeTypeException::forValue($name);
            }

            $normalized[$name] = $name === 'charset' ? strtolower($value) : $value;
        }

        $this->parameters = $normalized;
    }

    public function has(string $name): bool
    {
        return isset($this->parameters[strtolower($name)]);
    }

    public function get(string $name): ?string
    {
        return $this->parameters[strtolower($name)] ?? null;
    }

    public function isEmpty(): bool
    {
        return $this->parameters === [];
    }

    public function count(): int
    {
        return count($this->parameters);
    }

    public function offsetExists(mixed $offset): bool
    {
        return (is_string($offset) || is_int($offset)) && $this->has((string) $offset);
    }

    public function offsetGet(mixed $offset): ?string
    {
        return is_string($offset) || is_int($offset) ? $this->get((string) $offset) : null;
    }

    /** @throws \LogicException Parameters are immutable. */
    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \LogicException('MIME type parameters are immutable.');
    }

    /** @throws \LogicException Parameters are immutable. */
    public function offsetUnset(mixed $offset): never
    {
        throw new \LogicException('MIME type parameters are immutable.');
    }

    /** @return \Traversable<string, string> Normalized names in insertion order. */
    public function getIterator(): \Traversable
    {
        foreach ($this->parameters as $name => $value) {
            yield (string) $name => $value;
        }
    }
}
