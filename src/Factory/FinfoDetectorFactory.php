<?php

declare(strict_types=1);

namespace Componenta\Detector\Factory;

use Componenta\Detector\Exception\DetectorConfigurationException;
use Componenta\Detector\FinfoDetector;
use Componenta\Detector\MimeMapInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/** @internal Container integration; use FinfoDetector directly without DI. */
final class FinfoDetectorFactory
{
    /**
     * @param array<array-key, mixed> $parameters Container invocation parameters; configuration is resolved from the map binding.
     * @throws ContainerExceptionInterface
     * @throws DetectorConfigurationException
     */
    public static function create(ContainerInterface $container, array $parameters = []): FinfoDetector
    {
        $map = $container->get(MimeMapInterface::class);
        if (!$map instanceof MimeMapInterface) {
            throw new DetectorConfigurationException('The configured MIME map must implement MimeMapInterface.');
        }

        return new FinfoDetector($map);
    }
}
