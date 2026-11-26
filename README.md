# Componenta MIME Type Detector

MIME type and file extension detection with immutable value objects, a configurable extension catalog and PHP fileinfo.

## Installation

```bash
composer require componenta/mimetype-detector
```

Requires PHP 8.4+, ext-fileinfo, PSR-7 2.x and Componenta Config 4.x. The package works without a DI container; its ConfigProvider integrates it with Componenta applications.

## MIME type and extension values

```php
use Componenta\Detector\Ext;
use Componenta\Detector\MimeType;

$mime = new MimeType('Text/HTML; charset=UTF-8; name="a;b.html"');

$mime->value;                 // text/html
$mime->type;                  // text
$mime->subtype;               // html
$mime->charset;               // utf-8
$mime->boundary;              // null
$mime->parameters;            // ['charset' => 'utf-8', 'name' => 'a;b.html']
$mime->raw;                   // original input
$mime->getParameter('NAME');  // a;b.html
$mime->hasParameters();       // true
(string) $mime;               // text/html, without parameters

$mime->equals('TEXT/HTML');   // true
$mime->equals('text/*');      // false
$mime->matches('text/*');     // true
$mime->matches('*/*');        // true

$ext = new Ext('.TAR.GZ');
$ext->value;                  // tar.gz
$ext->withDot();              // .tar.gz
$ext->equals('tar.gz');       // true
```

MimeType parses concrete type/subtype names using the RFC 6838 name grammar and HTTP media type parameter syntax (RFC 9110). Quoted strings, escapes, semicolons inside quoted values and empty quoted values are supported. Names and charset labels are case-insensitive; other parameter values retain their case. Duplicate parameter names, wildcards in concrete types, malformed parameters and prohibited control characters are rejected. MimeType::isValid() and MimeType::tryCreate() use the same parser; the constructor throws InvalidMimeTypeException.

equals() compares normalized type/subtype and deliberately ignores parameters. matches() supports exact bare types, type wildcards and the universal wildcard; it does not parse an Accept header, parameter constraints or quality weights. Both methods accept multiple candidates and return false for no match.

The category methods isImage(), isAudio(), isVideo(), isText(), isApplication(), isModel(), isMessage(), isMultipart() and isMedia() classify the MIME type name. isFont() also recognizes known legacy application/font labels. These are classifications, not content validation. The toImageEnum(), toAudioEnum(), toVideoEnum(), toTextEnum(), toApplicationEnum(), toFontEnum(), toModelEnum(), toMessageEnum() and toMultipartEnum() methods return the corresponding enum case or null.

Ext accepts a simple or compound ASCII extension, optionally prefixed by a single dot. Empty values, paths and malformed extensions are rejected with InvalidArgumentException; Ext::tryCreate() returns null instead. Neither value object performs I/O or creates detection services.

## Detection

```php
use Componenta\Detector\FinfoDetector;

$detector = new FinfoDetector(maxBytes: 8192);

$mime = $detector->detectMimeType('plain text');
$mime?->value; // text/plain

$ext = $detector->detectExtension('plain text');
$ext?->value;  // txt

$mime = $detector->detectFileMimeType('/path/to/file.txt');
$ext = $detector->detectFileExtension('/path/to/file.txt');

// For a PSR-7 StreamInterface:
$mime = $detector->detectMimeType($stream);
```

All four detection methods return nullable value objects, never a string or boolean. Raw strings are content, not paths. File and stream reads and string detection use the same configurable positive byte limit (8192 by default). Seekable streams are read from the beginning and their original position is restored, including on read failures when restoration is possible. Non-seekable streams are consumed from the current position. Failure to restore the original position throws StreamPositionRestoreException: getPrevious() contains the restoration error, and readFailure retains any earlier read failure. A read that makes no progress before EOF fails rather than spinning. Use blocking, readable streams with appropriate timeouts.

File methods accept caller-authorized regular local filesystem paths; directories, special files and stream wrapper URLs are rejected. Missing files throw FileNotFoundException, invalid or unreadable file sources throw FileNotReadableException, and other I/O or detection failures throw DetectorException. On POSIX, the file is opened with a non-blocking open flag and its opened handle is checked to mitigate FIFO substitution between path checks and opening. A file path is not an access control boundary: enforce authorized paths in the application, including symlink policy and races. Empty content returns null. The backend may return application/octet-stream when it cannot identify a more specific type.

Fileinfo provides a heuristic result, which may vary across libmagic/PHP versions and with the inspected prefix. Increasing the byte limit can improve detection of some structured formats but cannot guarantee complete format validation. Extension detection consults only the configured MIME map, selecting its primary extension. It never trusts the file name, and returns null if the detected type has no mapped extension.

## MIME map

```php
use Componenta\Detector\FinfoDetector;
use Componenta\Detector\MimeMap;

$map = new MimeMap();
$map->getExtension('image/jpeg');      // jpg
$map->getExtensions('image/jpeg');     // ['jpg', 'jpeg', 'jpe']
$map->getMimeType('.JPG');             // image/jpeg
$map->getMimeTypes('xml');             // includes application/xml and text/xml
$map->hasMimeType('text/plain');       // true
$map->hasExtension('jpg');             // true
$map->isValidExtension('image/jpeg', 'jpg'); // true

$map->extend(['application/x-custom' => ['.CUSTOM']]);
$map->extend(['text/plain' => ['text']], override: true);
$map->remove('application/x-custom');
$map->setMap(['text/plain' => ['txt']]);

$map->getAllMimeTypes();
$map->getAllExtensions();
$map->getMimeTypesByCategory('text');

$detector = new FinfoDetector($map);
```

Map keys use the normalized concrete media type without parameters. Extension lookups and writes use the same Ext normalization. Associations are ordered: the first extension is preferred, and reverse lookup returns the first MIME type for that extension. getMimeTypes() returns all associated types in order, including legacy labels where applicable. Known types may have an empty extension list. Mutations are atomic; invalid input leaves the previous map and index intact. MIME-to-extension mappings are not claims that two different media types are semantically equivalent.

## Enumerations and catalog

The enums under Componenta\Detector\MimeTypes provide canonical lower-case media type values, values() and category-specific curated subsets (for example, Image::common() and Image::commonWebFormats()). Deprecated duplicate case names remain constants referencing the canonical cases. commonWebFormats() indicates common browser-oriented formats, not their safety or guaranteed browser support.

The editable source is resources/mime-catalog.json. It includes extension associations, legacy extension aliases and enum member names; it is a curated local catalog, not a complete IANA registry. Generate and verify PHP files with:

```bash
composer catalog-generate
composer catalog-check
```

## Dependency injection

ConfigProvider registers MimeMap as the default MimeMapInterface, and FinfoDetector via a factory that resolves that interface from the container. DetectorInterface combines four narrow contracts: MimeTypeDetectorInterface, ExtensionDetectorInterface, FileMimeTypeDetectorInterface and FileExtensionDetectorInterface. Each resolves to the same detector service according to the container's service lifetime policy. Override the map binding to customize extension resolution.

To verify integration in a consumer with Config 4, DI 6 and this package version installed:

```bash
php tests/integration/config-consumer.php /path/to/consumer/vendor/autoload.php
```

## Security

MIME and extension detection alone cannot establish file safety, authenticity or full format validity. Never authorize uploads or inline web rendering using only these values. Validate content independently, use an explicit allowlist, apply size limits and use appropriate response headers, isolation and storage policy. HTML, JavaScript, SVG and other types can contain active content.

## Development

```bash
composer install
composer check
composer bench
```

Tests use Pest. PHPStan analyzes the production source at its maximum level; PHPBench measures the principal public operations. Run the checks with both PHP 8.4 and 8.5. The release workflow verifies a release commit before publishing.
