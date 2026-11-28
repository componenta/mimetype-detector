<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Config\ConfigKey;
use Componenta\Detector\{ConfigProvider, DetectorInterface, ExtensionDetectorInterface, ExtensionResolverInterface, FileExtensionDetectorInterface, FileMimeTypeDetectorInterface, FinfoDetector, MimeMap, MimeMapInterface, MimeTypeDetectorInterface};
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

test('provider registers factory and all aliases', function (): void {
    $dependencies = (new ConfigProvider())()[ConfigKey::DEPENDENCIES];
    Assert::assertContains(MimeMap::class, $dependencies[ConfigKey::INVOKABLES]);
    Assert::assertNotContains(FinfoDetector::class, $dependencies[ConfigKey::INVOKABLES]);
    Assert::assertSame(MimeMap::class, $dependencies[ConfigKey::ALIASES][MimeMapInterface::class]);
    Assert::assertSame(MimeMapInterface::class, $dependencies[ConfigKey::ALIASES][ExtensionResolverInterface::class]);
    foreach ([DetectorInterface::class, MimeTypeDetectorInterface::class, FileMimeTypeDetectorInterface::class, ExtensionDetectorInterface::class, FileExtensionDetectorInterface::class] as $interface) {
        Assert::assertSame(FinfoDetector::class, $dependencies[ConfigKey::ALIASES][$interface]);
    }
    $factory = $dependencies[ConfigKey::FACTORIES][FinfoDetector::class];
    $map = (new MimeMap())->setMap(['text/plain' => ['custom']]);
    $container = $this->createMock(ContainerInterface::class);
    $container->expects(TestCase::once())->method('get')->with(ExtensionResolverInterface::class)->willReturn($map);
    $detector = $factory($container, []);
    Assert::assertInstanceOf(DetectorInterface::class, $detector);
    Assert::assertSame('custom', $detector->detectExtension('plain text')?->value);
});


test('an invalid container extension resolver binding is a configuration failure', function (): void {
    $factory = (new ConfigProvider())()[ConfigKey::DEPENDENCIES][ConfigKey::FACTORIES][FinfoDetector::class];
    $container = $this->createMock(ContainerInterface::class);
    $container->expects(TestCase::once())->method('get')->with(ExtensionResolverInterface::class)->willReturn(new \stdClass());
    $this->expectException(\Componenta\Detector\Exception\DetectorConfigurationException::class);
    $factory($container, []);
});
