<?php

declare(strict_types=1);

namespace Componenta\Detector;

use Psr\Container\ContainerInterface;

/** @internal Container integration; use FinfoDetector directly without DI. */
final class FinfoDetectorFactory
{
    public static function create(ContainerInterface $container): FinfoDetector
    {
        $map = $container->get(MimeMapInterface::class);
        if (!$map instanceof MimeMapInterface) {
            throw new \UnexpectedValueException('The configured MIME map must implement MimeMapInterface.');
        }

        return new FinfoDetector($map);
    }
}
