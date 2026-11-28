<?php

declare(strict_types=1);

use Componenta\Config\ConfigFactory;
use Componenta\Config\Environment;
use Componenta\DI\ContainerFactory;
use Componenta\Detector\{ConfigProvider, DetectorInterface, ExtensionDetectorInterface, ExtensionResolverInterface, FileExtensionDetectorInterface, FileMimeTypeDetectorInterface, FinfoDetector, MimeMap, MimeMapInterface, MimeTypeDetectorInterface};

if ($argc !== 2 || !is_file($argv[1])) {
    fwrite(STDERR, "Usage: php tests/integration/config-consumer.php /path/to/consumer/vendor/autoload.php\n");
    exit(2);
}
require $argv[1];

$composition = (new ConfigFactory())->create(new Environment([]), new ConfigProvider());
$container = (new ContainerFactory())->create($composition->config, $composition->dependencies)->container;
$detector = $container->get(DetectorInterface::class);
$map = $container->get(MimeMapInterface::class);
if (!$detector instanceof DetectorInterface || !$map instanceof MimeMap
    || $container->get(ExtensionResolverInterface::class) !== $map) {
    throw new RuntimeException('Config must register the public detector and default MIME map services.');
}

foreach ([FinfoDetector::class, MimeTypeDetectorInterface::class, FileMimeTypeDetectorInterface::class, ExtensionDetectorInterface::class, FileExtensionDetectorInterface::class] as $id) {
    if ($container->get($id) !== $detector) {
        throw new RuntimeException('Detector aliases must resolve to the same configured service: ' . $id);
    }
}

if ($detector->detectMimeType('plain text')?->value !== 'text/plain'
    || $detector->detectExtension('plain text')?->value !== 'txt'
    || $map->getMimeType('jpg') !== 'image/jpeg') {
    throw new RuntimeException('Configured MIME services must retain detection and map behavior.');
}

// A mutation on the container-owned map must be visible to the existing detector.
$map->extend(['text/plain' => ['custom']], override: true);
if ($detector->detectExtension('plain text')?->value !== 'custom') {
    throw new RuntimeException('Detector does not use the map resolved from its container.');
}

// Replace the full map binding with a configured map and verify the alias chain.
$customMap = (new MimeMap())->setMap(['text/plain' => ['mapped']]);
$mapProvider = new class($customMap) extends \Componenta\Config\ConfigProvider {
    public function __construct(private readonly MimeMapInterface $map) {}

    protected function getServices(): array
    {
        return ['mime.test.map' => $this->map];
    }

    protected function getAliases(): array
    {
        return [MimeMapInterface::class => 'mime.test.map'];
    }
};
$composition = (new ConfigFactory())->create(new Environment([]), new ConfigProvider(), $mapProvider);
$container = (new ContainerFactory())->create($composition->config, $composition->dependencies)->container;
if ($container->get(ExtensionResolverInterface::class) !== $customMap
    || $container->get(DetectorInterface::class)->detectExtension('plain text')?->value !== 'mapped') {
    throw new RuntimeException('Replacing the full map binding must affect extension detection.');
}

// An independent resolver need not implement or replace the full map.
$customResolver = new class implements ExtensionResolverInterface {
    public function getExtension(string $mimeType): ?string
    {
        return $mimeType === 'text/plain' ? 'resolved' : null;
    }
};
$resolverProvider = new class($customResolver) extends \Componenta\Config\ConfigProvider {
    public function __construct(private readonly ExtensionResolverInterface $resolver) {}

    protected function getServices(): array
    {
        return ['mime.test.resolver' => $this->resolver];
    }

    protected function getAliases(): array
    {
        return [ExtensionResolverInterface::class => 'mime.test.resolver'];
    }
};
$composition = (new ConfigFactory())->create(new Environment([]), new ConfigProvider(), $resolverProvider);
$container = (new ContainerFactory())->create($composition->config, $composition->dependencies)->container;
if ($container->get(ExtensionResolverInterface::class) !== $customResolver
    || !$container->get(MimeMapInterface::class) instanceof MimeMap
    || $container->get(DetectorInterface::class)->detectExtension('plain text')?->value !== 'resolved') {
    throw new RuntimeException('Replacing only the extension resolver must affect detection without replacing the full map.');
}

echo 'Config 4 + DI 6 MIME provider passed.', PHP_EOL;
