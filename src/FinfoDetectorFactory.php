<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Psr\Container\ContainerInterface;

/** @internal Container integration; use FinfoDetector directly without DI. */
final class FinfoDetectorFactory
{
    /** @param array<array-key, mixed> $parameters Container invocation parameters; configuration is resolved from the map binding. */
    public static function create(ContainerInterface $container, array $parameters = []): FinfoDetector
    {
        $map = $container->get(MimeMapInterface::class);
        if (!$map instanceof MimeMapInterface) {
            throw new \UnexpectedValueException('The configured MIME map must implement MimeMapInterface.');
        }

        return new FinfoDetector($map);
    }
}
