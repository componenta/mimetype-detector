<?php

declare(strict_types=1);

namespace Componenta\Detector\Tests;

use Componenta\Config\Config;
use Componenta\Config\ConfigKey;
use Componenta\Config\ContainerValue;
use Componenta\Config\Environment;
use Componenta\Config\Exception\InvalidContainerValueException;
use Componenta\Detector\Exception\DetectorConfigurationException;
use Componenta\Detector\Factory\FinfoDetectorFactory;
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
    $factoryClass = $dependencies[ConfigKey::FACTORIES][FinfoDetector::class];
    Assert::assertSame(FinfoDetectorFactory::class, $factoryClass);
    $factory = new $factoryClass();
    $map = (new MimeMap())->setMap(['text/plain' => ['custom']]);
    $container = $this->createMock(ContainerInterface::class);
    $container->expects(TestCase::once())->method('get')->with(ExtensionResolverInterface::class)->willReturn($map);
    $context = new ContainerValue($container, new Config([], new Environment([])));
    $detector = $factory($context, []);
    Assert::assertInstanceOf(DetectorInterface::class, $detector);
    Assert::assertSame('custom', $detector->detectExtension('plain text')?->value);
});


test('the invokable factory validates resolver bindings through ContainerValue', function (mixed $binding): void {
    $factory = new FinfoDetectorFactory();
    $container = $this->createMock(ContainerInterface::class);
    $container->expects(TestCase::once())->method('get')->with(ExtensionResolverInterface::class)->willReturn($binding);
    $context = new ContainerValue($container, new Config([], new Environment([])));

    try {
        $factory($context);
        Assert::fail('Expected a detector configuration failure.');
    } catch (DetectorConfigurationException $error) {
        Assert::assertInstanceOf(InvalidContainerValueException::class, $error->getPrevious());
        Assert::assertStringContainsString(ExtensionResolverInterface::class, $error->getMessage());
    }
})->with([
    'unrelated object' => [new \stdClass()],
    'null' => [null],
    'false' => [false],
    'string' => ['invalid'],
]);

test('container lookup failures propagate unchanged from the factory', function (): void {
    $failure = new class('missing resolver') extends \RuntimeException implements \Psr\Container\NotFoundExceptionInterface {};
    $container = $this->createMock(ContainerInterface::class);
    $container->expects(TestCase::once())->method('get')->with(ExtensionResolverInterface::class)->willThrowException($failure);

    try {
        (new FinfoDetectorFactory())(new ContainerValue($container, new Config([], new Environment([]))));
        Assert::fail('Expected the original container lookup failure.');
    } catch (\Psr\Container\NotFoundExceptionInterface $error) {
        Assert::assertSame($failure, $error);
    }
});

test('the factory constructs fresh detectors using the container-owned resolver', function (): void {
    $resolver = new class implements ExtensionResolverInterface {
        public function getExtension(string $mimeType): ?string
        {
            return $mimeType === 'text/plain' ? 'fresh' : null;
        }
    };
    $container = $this->createMock(ContainerInterface::class);
    $container->expects(TestCase::exactly(2))->method('get')->with(ExtensionResolverInterface::class)->willReturn($resolver);
    $context = new ContainerValue($container, new Config([], new Environment([])));
    $factory = new FinfoDetectorFactory();

    $first = $factory($context);
    $second = $factory($context);
    Assert::assertNotSame($first, $second);
    Assert::assertSame('fresh', $first->detectExtension('plain text')?->value);
    Assert::assertSame('fresh', $second->detectExtension('plain text')?->value);
});
