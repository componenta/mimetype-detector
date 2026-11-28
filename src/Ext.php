<?php

declare(strict_types=1);

namespace Componenta\Detector;

final readonly class Ext implements \Stringable
{
    public string $value;

    /** @throws \InvalidArgumentException */
    public function __construct(string $value)
    {
        $normalized = strtolower(str_starts_with($value, '.') ? substr($value, 1) : $value);

        // A simple or compound suffix, not a filename or filesystem path.
        if (preg_match('/\A[a-z0-9][a-z0-9_+-]*(?:\.[a-z0-9][a-z0-9_+-]*)*\z/D', $normalized) !== 1) {
            throw new \InvalidArgumentException('Invalid file extension.');
        }

        $this->value = $normalized;
    }

    public static function tryCreate(string $value): ?self
    {
        try {
            return new self($value);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self|string ...$extensions): bool
    {
        foreach ($extensions as $extension) {
            $candidate = $extension instanceof self ? $extension : self::tryCreate($extension);
            if ($candidate !== null && $this->value === $candidate->value) {
                return true;
            }
        }
        return false;
    }

    public function withDot(): string
    {
        return '.' . $this->value;
    }
}
