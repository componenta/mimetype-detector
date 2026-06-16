# Componenta MIME Type Detector

MIME type and file-extension detection with value objects and a local MIME map.

## Installation

```bash
composer require componenta/mimetype-detector
```

The package declares `Componenta\Detector\ConfigProvider` in `extra.componenta.config-providers`.
When `componenta/composer-plugin` is installed, the provider is added to the generated provider list automatically.

## Requirements

- PHP 8.4+
- `fileinfo` extension
- PSR-7 stream interfaces from `psr/http-message`

## Related Packages

This package is standalone but requires the PHP `fileinfo` extension.

| Package | Why it may be used nearby |
|---|---|
| `componenta/validation` | MIME/file rules can use the detector for uploaded files. |
| `componenta/http-responder` | File responses can use MIME types for content headers. |
| `componenta/image-converter` | Media flows can detect a format before conversion. |

## What It Provides

- `MimeType`: parsed MIME type value object.
- `Ext`: normalized file extension value object.
- `MimeMapInterface` and `MimeMap`: MIME-to-extension and extension-to-MIME mapping.
- `FinfoDetector`: `fileinfo`-based detector for strings, streams, and files.
- `DetectorInterface`: combined contract for MIME type, extension, file MIME type, and file extension detection.
- Smaller contracts: `MimeTypeDetectorInterface`, `ExtensionDetectorInterface`, `FileMimeTypeDetectorInterface`, and `FileExtensionDetectorInterface`.
- `ConfigProvider`: registers the default detector and map services in Componenta applications.
- Typed exceptions for missing/unreadable files and invalid MIME types.

## MIME Types

```php
use Componenta\Detector\MimeType;

$mime = new MimeType('text/html; charset=utf-8');

$mime->value;      // text/html
$mime->type;       // text
$mime->subtype;    // html
$mime->charset;    // utf-8
$mime->isText();   // true
$mime->isWebSafe(); // true
```

Invalid MIME strings throw `InvalidMimeTypeException`.

## Extensions

```php
use Componenta\Detector\Ext;

$ext = new Ext('.JPG');

$ext->value;    // jpg
$ext->withDot(); // .jpg
$ext->isImage(); // true
```

## MIME Map

```php
use Componenta\Detector\MimeMap;

$map = new MimeMap();

$map->getExtension('image/jpeg'); // jpg
$map->getMimeType('jpg');         // image/jpeg

$map->extend(['application/x-custom' => ['custom']]);
```

## Detection

```php
use Componenta\Detector\FinfoDetector;
use Componenta\Detector\MimeTypeDetectorInterface;

/** @var MimeTypeDetectorInterface $detector */
$detector = new FinfoDetector();

$detector->detectMimeType('plain text'); // text/plain
$detector->detectExtension('plain text'); // txt

$detector->detectFileMimeType('/path/to/file.txt');
$detector->detectFileExtension('/path/to/file.txt');
```

Pass `asObject: true` to return `MimeType` or `Ext` objects.

`FinfoDetector` reads up to 8192 bytes from files and streams. Seekable streams are rewound for detection and then restored to their previous position. Non-seekable streams are read from the current position, so callers should provide them at the correct offset.

File detection throws `FileNotFoundException`, `FileNotReadableException`, or `DetectorException` for I/O failures.
