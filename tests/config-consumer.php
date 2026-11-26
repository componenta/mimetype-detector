<?php

declare(strict_types=1);

require $argv[1];

use Componenta\Config\ConfigFactory;
use Componenta\Config\Environment;
use Componenta\DI\ContainerFactory;
use Componenta\Detector\ConfigProvider;
use Componenta\Detector\DetectorInterface;
use Componenta\Detector\MimeMapInterface;
use Componenta\Detector\MimeTypeDetectorInterface;

$composition = (new ConfigFactory())->create(new Environment([]), new ConfigProvider());
$container = (new ContainerFactory())->create($composition->config, $composition->dependencies)->container;
$detector = $container->get(DetectorInterface::class);
$map = $container->get(MimeMapInterface::class);
if (!$detector instanceof DetectorInterface || !$map instanceof MimeMapInterface) {
    throw new RuntimeException('Config must register the public detector and MIME map services.');
}
if ($container->get(MimeTypeDetectorInterface::class) !== $detector) {
    throw new RuntimeException('Detector aliases must resolve to the same configured service.');
}
if ($detector->detectMimeType('plain text') !== 'text/plain'
    || $detector->detectExtension('plain text') !== 'txt'
    || $map->getMimeType('jpg') !== 'image/jpeg') {
    throw new RuntimeException('Configured MIME services must retain detection and map behavior.');
}

echo 'Config 4 + DI 6 MIME provider passed.', PHP_EOL;
