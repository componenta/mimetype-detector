<?php

declare(strict_types=1);

namespace Componenta\Detector\Factory;

use Componenta\Config\ContainerValue;
use Componenta\Config\Exception\InvalidContainerValueException;
use Componenta\Detector\Exception\DetectorConfigurationException;
use Componenta\Detector\ExtensionResolverInterface;
use Componenta\Detector\FinfoDetector;
use Psr\Container\ContainerExceptionInterface;

/** @internal Container integration; use FinfoDetector directly without DI. */
final readonly class FinfoDetectorFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws DetectorConfigurationException
     */
    public function __invoke(ContainerValue $container): FinfoDetector
    {
        try {
            $resolver = $container->get(
                ExtensionResolverInterface::class,
                ExtensionResolverInterface::class,
            );
        } catch (InvalidContainerValueException $error) {
            throw new DetectorConfigurationException(
                'The configured extension resolver must implement ExtensionResolverInterface.',
                previous: $error,
            );
        }

        return new FinfoDetector($resolver);
    }
}
