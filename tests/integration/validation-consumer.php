<?php

declare(strict_types=1);

use Componenta\Detector\FinfoDetector;
use Componenta\Validation\Context;
use Componenta\Validation\Error\ErrorMessageCollectorInterface;
use Componenta\Validation\Rule\MimeType;
use GuzzleHttp\Psr7\UploadedFile;
use GuzzleHttp\Psr7\Utils;

if ($argc !== 2 || !is_file($argv[1])) {
    fwrite(STDERR, "Usage: php tests/integration/validation-consumer.php /path/to/consumer/vendor/autoload.php\n");
    exit(2);
}
require $argv[1];

$stream = Utils::streamFor('plain text');
try {
    $stream->seek(3);
    $upload = new UploadedFile($stream, 10, UPLOAD_ERR_OK, 'spoofed.png', 'image/png');
    $detector = new FinfoDetector();
    $textRule = new MimeType($detector, ['text/plain']);
    $imageRule = new MimeType($detector, ['image/png']);

    if (!$textRule($upload)
        || $textRule->validate($upload, Context::currentPath('attachment')) !== true
        || $stream->tell() !== 3
        || $imageRule($upload)
        || !$imageRule->validate($upload, Context::currentPath('attachment')) instanceof ErrorMessageCollectorInterface
        || $stream->tell() !== 3) {
        throw new RuntimeException('Validation must inspect actual uploaded bytes, reject spoofed client metadata and preserve the stream position.');
    }
} finally {
    $stream->close();
}

echo 'Validation MIME rule passed.', PHP_EOL;
