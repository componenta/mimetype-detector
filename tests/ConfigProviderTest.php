<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Config\ConfigKey;
use Componenta\Detector\{ConfigProvider, DetectorInterface, ExtensionDetectorInterface, FileExtensionDetectorInterface, FileMimeTypeDetectorInterface, FinfoDetector, MimeMap, MimeMapInterface, MimeTypeDetectorInterface};
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ConfigProviderTest extends TestCase
{
    public function testProviderRegistersFactoryAndAllAliases(): void
    {
        $dependencies = (new ConfigProvider())()[ConfigKey::DEPENDENCIES];
        self::assertContains(MimeMap::class, $dependencies[ConfigKey::INVOKABLES]);
        self::assertNotContains(FinfoDetector::class, $dependencies[ConfigKey::INVOKABLES]);
        self::assertSame(MimeMap::class, $dependencies[ConfigKey::ALIASES][MimeMapInterface::class]);
        foreach ([DetectorInterface::class, MimeTypeDetectorInterface::class, FileMimeTypeDetectorInterface::class, ExtensionDetectorInterface::class, FileExtensionDetectorInterface::class] as $interface) {
            self::assertSame(FinfoDetector::class, $dependencies[ConfigKey::ALIASES][$interface]);
        }
        $factory = $dependencies[ConfigKey::FACTORIES][FinfoDetector::class];
        $map = (new MimeMap())->setMap(['text/plain' => ['custom']]);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())->method('get')->with(MimeMapInterface::class)->willReturn($map);
        $detector = $factory($container, []);
        self::assertInstanceOf(DetectorInterface::class, $detector);
        self::assertSame('custom', $detector->detectExtension('plain text')?->value);
    }
}
