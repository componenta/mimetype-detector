# Componenta MIME Type Detector

[Русский](README.ru.md)

Detect MIME types and preferred extensions from content using a configurable extension map.

## Installation

```sh
composer require componenta/mimetype-detector:^2.0
```

Documentation for the revised version 2.0.0. Requires PHP 8.4+, ext-fileinfo, PSR-7 2.x and Componenta Config 4.x. A DI container is not required for standalone use. [Upgrade guide](UPGRADE.md).

## Quick start

```php
use Componenta\Detector\FinfoDetector;

$detector = new FinfoDetector();
$mime = $detector->detectMimeType('plain text');
$extension = $detector->detectExtension('plain text');

echo $mime?->value;       // text/plain
echo $extension?->value;  // txt
```

For a local file, use `detectFileMimeType($path)` or `detectFileExtension($path)`. A string passed to a content method always means bytes, not a path.

## Detection API

### [`FinfoDetector`](src/FinfoDetector.php)

Default implementation of all four detection contracts. Use directly or through a container.

#### `__construct()`

`__construct(?ExtensionResolverInterface $mimeMap = null, int $maxBytes = 8192)`

Creates a detector.

| Argument | Description |
| --- | --- |
| `$mimeMap` | Preferred-extension resolver. `null` uses the default `MimeMap`. |
| `$maxBytes` | Positive maximum number of bytes inspected for any source. |

**Exceptions:** `DetectorConfigurationException` if the limit is invalid or Fileinfo cannot initialize.


#### `detectMimeType()`

`detectMimeType(string|StreamInterface $content): ?MimeType`

Detects a MIME type from content.

| Argument | Description |
| --- | --- |
| `$content` | Content bytes or a PSR-7 stream. A string is not a path. |

**Returns:** `MimeType`, or `null` if no result is available.

**Exceptions:** `DetectorException`, `StreamExceptionInterface`.


#### `detectFileMimeType()`

`detectFileMimeType(string $filename): ?MimeType`

Detects a MIME type from the beginning of a local file.

| Argument | Description |
| --- | --- |
| `$filename` | An application-authorized path to a regular local file. |

**Returns:** `MimeType`, or `null` if no result is available.

**Exceptions:** `DetectorException`, `FileExceptionInterface`.


#### `detectExtension()`

`detectExtension(string|StreamInterface $content): ?Ext`

Resolves the preferred extension for the detected MIME type using the configured map.

| Argument | Description |
| --- | --- |
| `$content` | Content bytes or a PSR-7 stream. A string is not a path. |

**Returns:** `Ext`, or `null` if no MIME type or mapped extension is available.

**Exceptions:** `DetectorExceptionInterface`, `StreamExceptionInterface`.


#### `detectFileExtension()`

`detectFileExtension(string $filename): ?Ext`

Resolves the preferred extension from local file content. The filename is not used for detection.

| Argument | Description |
| --- | --- |
| `$filename` | An application-authorized path to a regular local file. |

**Returns:** `Ext`, or `null` if no MIME type or mapped extension is available.

**Exceptions:** `DetectorExceptionInterface`, `FileExceptionInterface`.


### Contracts

| Interface | Operations |
| --- | --- |
| [MimeTypeDetectorInterface](src/MimeTypeDetectorInterface.php) | `detectMimeType()` |
| [FileMimeTypeDetectorInterface](src/FileMimeTypeDetectorInterface.php) | `detectFileMimeType()` |
| [ExtensionDetectorInterface](src/ExtensionDetectorInterface.php) | `detectExtension()` |
| [FileExtensionDetectorInterface](src/FileExtensionDetectorInterface.php) | `detectFileExtension()` |
| [DetectorInterface](src/DetectorInterface.php) | All four operations |

Depend on the narrow contract you need. Custom implementations report detection failures through `DetectorExceptionInterface` and source acquisition failures through the relevant file or stream interface. Stream failures apply only to stream input.

### Sources and limits

All sources are inspected within `maxBytes`. Seekable streams are read from the beginning and their position is restored on success and failure. Non-seekable streams consume the inspected prefix from the current position. The caller retains ownership of the stream. Failure to restore position raises a distinct exception.

Local files are not modified. URLs, stream wrappers, directories and special files are rejected. Symlinks to regular files are allowed; the application must authorize the path. The detector checks file identity where available and may invalidate PHP's realpath cache if path resolution is stale. These checks do not eliminate filesystem races or replace authorization. A bounded prefix does not guarantee full-format validity, safety, processing time or absolute memory usage. Use readable blocking streams with appropriate timeouts.

Empty content yields `null`. Fileinfo may return `application/octet-stream` for unknown formats. Detection is heuristic and depends on PHP/libmagic and the inspected prefix.

## [`MimeType`](src/MimeType.php)

```php
use Componenta\Detector\MimeType;

$mime = new MimeType('Text/HTML; charset=UTF-8; name="a;b.html"');
echo $mime->value; // text/html
$mime->equals('text/html'); // true
$mime->matches('text/*');   // true
```

Immutable concrete MIME type value using RFC 6838 names and RFC 9110 parameter syntax.

| Property | Description |
| --- | --- |
| `value: string` | Normalized type/subtype without parameters. |
| `type: string` | Top-level type. |
| `subtype: string` | Subtype. |
| `parameters: MimeParameters` | Immutable collection of MIME parameters. |
| `raw: string` | Original input. |
| `charset: ?string` | Charset or null. |
| `boundary: ?string` | Multipart boundary or null. |

#### `__construct()`

`__construct(string $value)`

Validates and normalizes a concrete MIME type. Supports quoted parameters and escapes; rejects duplicates, wildcards and prohibited control characters.

| Argument | Description |
| --- | --- |
| `$value` | Media type string with optional parameters. |

**Exceptions:** `InvalidMimeTypeException`.


#### `tryCreate()`

`tryCreate(string $value): ?self`

Static method for non-throwing construction.

| Argument | Description |
| --- | --- |
| `$value` | Candidate string. |

**Returns:** The object, or `null` for invalid input.


#### `isValid()`

`isValid(string $value): bool`

Static method validating the same grammar as the constructor.

| Argument | Description |
| --- | --- |
| `$value` | Candidate string. |

**Returns:** `true` if valid.


#### `equals()`

`equals(self|string ...$types): bool`

Compares normalized type/subtype, ignoring parameters. Wildcards are not exact types.

| Argument | Description |
| --- | --- |
| `$types` | Zero or more candidate types. |

**Returns:** `true` if any matches; otherwise `false`.


#### `matches()`

`matches(string ...$ranges): bool`

Matches bare exact types, `image/*` or `*/*`. Does not parse Accept headers or quality weights.

| Argument | Description |
| --- | --- |
| `$ranges` | Candidate types and wildcards. |

**Returns:** `true` if any matches.


#### Classification and enums

Classification methods inspect the MIME type name, not content. All methods below take no arguments.

| Signature | Result |
| --- | --- |
| `__toString(): string` | Type/subtype without parameters. |
| `isImage(): bool` | Top-level type is `image`. |
| `isAudio(): bool` | Top-level type is `audio`. |
| `isVideo(): bool` | Top-level type is `video`. |
| `isText(): bool` | Top-level type is `text`. |
| `isApplication(): bool` | Top-level type is `application`. |
| `isModel(): bool` | Top-level type is `model`. |
| `isMessage(): bool` | Top-level type is `message`. |
| `isMultipart(): bool` | Top-level type is `multipart`. |
| `isFont(): bool` | Font, including known legacy application/font labels. |
| `isMedia(): bool` | Image, video or audio. |
| `toImageEnum(): ?Image` | Matching `Image` enum case or null. |
| `toAudioEnum(): ?Audio` | Matching `Audio` enum case or null. |
| `toVideoEnum(): ?Video` | Matching `Video` enum case or null. |
| `toTextEnum(): ?Text` | Matching `Text` enum case or null. |
| `toApplicationEnum(): ?Application` | Matching `Application` enum case or null. |
| `toFontEnum(): ?Font` | Matching `Font` enum case or null. |
| `toModelEnum(): ?Model` | Matching `Model` enum case or null. |
| `toMessageEnum(): ?Message` | Matching `Message` enum case or null. |
| `toMultipartEnum(): ?Multipart` | Matching `Multipart` enum case or null. |

## [`MimeParameters`](src/MimeParameters.php)

An immutable collection of decoded MIME parameters. Names are case insensitive and normalized to lowercase; values preserve their case except `charset`. An absent parameter returns `null`, whereas an explicitly empty value returns `''`. Implements `ArrayAccess`, `IteratorAggregate` and `Countable`.

```php
use Componenta\Detector\MimeType;

$parameters = (new MimeType('text/plain; charset=UTF-8; name="Example"'))->parameters;
$parameters->has('CHARSET'); // true
$parameters->get('charset'); // utf-8
$parameters['name']; // Example
isset($parameters['NAME']); // true
count($parameters); // 2
foreach ($parameters as $name => $value) {
    echo $name . ': ' . $value . PHP_EOL;
}
```

#### `__construct()`

`__construct(array $parameters = [])`

Creates a validated collection from decoded parameter names and string values, not raw quoted header syntax.

| Argument | Description |
| --- | --- |
| `$parameters` | Name-to-value map (`array<array-key, string>`). Defaults to an empty collection. Numeric keys represent numeric parameter names. |

**Exceptions:** `InvalidMimeTypeException` for malformed names, non-string values, prohibited control characters or duplicate names after normalization.

#### `has()`

`has(string $name): bool`

Checks whether the named parameter exists, including empty values.

| Argument | Description |
| --- | --- |
| `$name` | Parameter name; case insensitive. |

**Returns:** `true` if present.

#### `get()`

`get(string $name): ?string`

Looks up a parameter.

| Argument | Description |
| --- | --- |
| `$name` | Parameter name; case insensitive. |

**Returns:** Decoded value or `null` when absent.

#### `isEmpty()`

`isEmpty(): bool`

Returns `true` if the collection contains no parameters.

#### `count()`

`count(): int`

Returns the number of parameters. Also available as `count($parameters)`.

#### `offsetExists()`

`offsetExists(mixed $offset): bool`

Implements `isset($parameters[$offset])`. A string or integer is interpreted as a parameter name; other types return `false`.

#### `offsetGet()`

`offsetGet(mixed $offset): ?string`

Implements array-style reads. A string or integer is interpreted as a parameter name; absent names and other offset types return `null`.

#### `offsetSet()`

`offsetSet(mixed $offset, mixed $value): never`

Always throws `LogicException`: neither replacing nor appending a value is supported.

#### `offsetUnset()`

`offsetUnset(mixed $offset): never`

Always throws `LogicException`: parameters cannot be removed.

#### `getIterator()`

`getIterator(): Traversable`

Returns a fresh iterator over normalized string names and decoded string values in insertion order. Use `foreach` or `iterator_to_array()` to read a snapshot; PHP converts numeric string keys to integers when materializing an array.


## [`Ext`](src/Ext.php)

```php
use Componenta\Detector\Ext;

$ext = new Ext('.TAR.GZ');
echo $ext->value;     // tar.gz
echo $ext->withDot(); // .tar.gz
```

Immutable extension value. The `value: string` property contains the normalized suffix without a leading dot.

#### `__construct()`

`__construct(string $value)`

Accepts a simple or compound ASCII extension, optionally with a leading dot. Paths and empty or malformed values are invalid; this does not extract a suffix from a filename.

| Argument | Description |
| --- | --- |
| `$value` | Extension. |

**Exceptions:** `InvalidArgumentException`.


#### `tryCreate()`

`tryCreate(string $value): ?self`

Static construction without throwing for an invalid extension.

| Argument | Description |
| --- | --- |
| `$value` | Extension. |

**Returns:** Object or `null`.


#### `equals()`

`equals(self|string ...$extensions): bool`

Checks normalized extension equality.

| Argument | Description |
| --- | --- |
| `$extensions` | Candidate extensions. |

**Returns:** `true` if any matches.


#### `withDot()`

`withDot(): string`

Returns the extension with a leading dot.

**Returns:** String such as `.tar.gz`.


#### `__toString()`

`__toString(): string`

Returns the normalized extension without a leading dot.

**Returns:** String such as `tar.gz`.


## [`MimeMap`](src/MimeMap.php)

Configurable extension catalog. Lookup is defined by [MimeMapInterface](src/MimeMapInterface.php). Order matters: the first extension is preferred; reverse lookup selects the first MIME type. Mutations are atomic. An extension association is not evidence of type equivalence or content safety.

```php
use Componenta\Detector\FinfoDetector;
use Componenta\Detector\MimeMap;

$map = new MimeMap();
$map->extend(['application/x-custom' => ['.CUSTOM']]);
$map->getExtension('application/x-custom'); // custom
$detector = new FinfoDetector($map);
```

#### `getExtensions()`

`getExtensions(string $mimeType): array`

Gets the extensions for a MIME type.

| Argument | Description |
| --- | --- |
| `$mimeType` | Concrete MIME type; parameters are ignored for lookup. |

**Returns:** Ordered `list<string>`, or an empty list.


#### `getExtension()`

`getExtension(string $mimeType): ?string`

Gets the preferred extension.

| Argument | Description |
| --- | --- |
| `$mimeType` | Concrete MIME type; parameters are ignored for lookup. |

**Returns:** String or `null`.


#### `getMimeType()`

`getMimeType(string $extension): ?string`

Gets the first associated MIME type.

| Argument | Description |
| --- | --- |
| `$extension` | Extension, optionally prefixed by a dot. |

**Returns:** String or `null`.


#### `getMimeTypes()`

`getMimeTypes(string $extension): array`

Gets all MIME types associated with an extension.

| Argument | Description |
| --- | --- |
| `$extension` | Extension, optionally prefixed by a dot. |

**Returns:** Ordered `list<string>`, or an empty list.


#### `isValidExtension()`

`isValidExtension(string $mimeType, string $extension): bool`

Checks whether the association exists in the map.

| Argument | Description |
| --- | --- |
| `$mimeType` | Concrete MIME type; parameters are ignored for lookup. |
| `$extension` | Extension, optionally prefixed by a dot. |

**Returns:** `true` if associated.


#### `hasMimeType()`

`hasMimeType(string $mimeType): bool`

Checks registration even if the extension list is empty.

| Argument | Description |
| --- | --- |
| `$mimeType` | Concrete MIME type; parameters are ignored for lookup. |

**Returns:** `true` if registered.


#### `hasExtension()`

`hasExtension(string $extension): bool`

Checks for at least one associated media type.

| Argument | Description |
| --- | --- |
| `$extension` | Extension, optionally prefixed by a dot. |

**Returns:** `true` if associated.


#### `getAllMimeTypes()`

`getAllMimeTypes(): array`

Gets all registered MIME types.

**Returns:** `list<string>` in map order.


#### `getAllExtensions()`

`getAllExtensions(): array`

Gets unique extensions.

**Returns:** `list<string>` in first-registration order.


#### `getMimeTypesByCategory()`

`getMimeTypesByCategory(string $category): array`

Gets mappings for a top-level media type.

| Argument | Description |
| --- | --- |
| `$category` | For example, `image` or `text`. |

**Returns:** `array<string, list<string>>`.


#### `__construct()`

`__construct()`

Creates a map with the default local catalog.


#### `extend()`

`extend(array $map, bool $override = false): self`

Adds or merges mappings; preserves existing order.

| Argument | Description |
| --- | --- |
| `$map` | MIME types mapped to extension lists (`array<string, list<string>>`). |
| `$override` | `true` replaces an existing type's list; defaults to merging. |

**Returns:** The same map instance.

**Exceptions:** `InvalidArgumentException`.


#### `remove()`

`remove(string ...$mimeTypes): self`

Atomically removes mappings for supplied types.

| Argument | Description |
| --- | --- |
| `$mimeTypes` | Concrete MIME type; parameters are ignored for lookup. |

**Returns:** The same map instance.

**Exceptions:** `InvalidMimeTypeException`.


#### `setMap()`

`setMap(array $map): self`

Atomically replaces the entire map.

| Argument | Description |
| --- | --- |
| `$map` | MIME types mapped to extension lists (`array<string, list<string>>`). |

**Returns:** The same map instance.

**Exceptions:** `InvalidArgumentException`.


#### `getDefaultMap()`

`getDefaultMap(): array`

Statically returns the default associations.

**Returns:** `array<string, list<string>>`.


## Exceptions

Library exceptions live in `Componenta\Detector\Exception`. The categories are independent.

| Contract | Purpose |
| --- | --- |
| `DetectorExceptionInterface` | Detection, extension resolution and configuration failures. |
| `FileExceptionInterface` | File source acquisition failures. |
| `StreamExceptionInterface` | Stream reading or positioning failures. |
| `StreamPositionRestoreExceptionInterface` | Position restoration failure; specializes the stream contract. |

| Exception | Cause |
| --- | --- |
| `DetectorException` | Unable to detect a type or resolve an extension. |
| `DetectorConfigurationException` | Invalid configuration; extends InvalidArgumentException. |
| `FileUnavailableException` | File is missing or inaccessible during resolution. |
| `InvalidFileException` | Invalid file path or type. |
| `FileReadException` | Failure opening, reading, inspecting the opened file or closing it. |
| `StreamReadException` | Stream reading or initial positioning failure. |
| `StreamPositionRestoreException` | The original position could not be restored. |
| `InvalidMimeTypeException` | Invalid MIME type; extends InvalidArgumentException. |

On position restoration failure, `getPrevious()` contains the restoration cause and `readFailure` retains an earlier failure, if any. If file closing also fails, `FileReadException::$readFailure` preserves the preceding failure. Programming errors are not converted to normal negative results. Unexpected stream state-query failures propagate unchanged if position restoration succeeds.

```php
use Componenta\Detector\Exception\DetectorExceptionInterface;
use Componenta\Detector\Exception\FileExceptionInterface;

try {
    $mime = $detector->detectFileMimeType($path);
} catch (FileExceptionInterface $error) {
    // File source failure.
} catch (DetectorExceptionInterface $error) {
    // Detection failure.
}
```

## MIME type enumerations

The enums in `Componenta\Detector\MimeTypes` contain canonical lowercase types and a `values()` method. Curated subsets, e.g. `Image::commonWebFormats()`, are not security checks or guarantees of browser support. The catalog is local, not an exhaustive IANA registry.


### Common enum methods

All nine enums expose the following methods.

#### `values()`

`values(): array`

Returns the string values of canonical cases in declaration order, without duplicates or aliases (`list<string>`). No arguments.

#### `from()`

`from(int|string $value): static`

Native PHP backed-enum method. Returns the case with the exact backed value. `$value` is a canonical lowercase MIME type without parameters. Throws `ValueError` when the case does not exist; strict PHP callers must pass a string or a `TypeError` is raised.

#### `tryFrom()`

`tryFrom(int|string $value): ?static`

Accepts the same values as `from()` but returns `null` when no case exists; strict callers must still pass a string.

#### `cases()`

`cases(): array`

Returns canonical enum cases in declaration order (`list<self>`). No arguments.

### Category classification

Each method below is static. The `$mimeType` argument is a concrete MIME type string with optional parameters. Type names are case insensitive. Invalid types, wildcards and unrelated categories return `false`; membership in the category does not require a corresponding enum case.

| Enum | Signature | Condition for `true` |
| --- | --- | --- |
| [Application](src/MimeTypes/Application.php) | `isApplication(string $mimeType): bool` | A type in the `application` category. |
| [Audio](src/MimeTypes/Audio.php) | `isAudio(string $mimeType): bool` | A type in the `audio` category. |
| [Font](src/MimeTypes/Font.php) | `isFont(string $mimeType): bool` | A `font/*` type or a known legacy font type. |
| [Image](src/MimeTypes/Image.php) | `isImage(string $mimeType): bool` | A type in the `image` category. |
| [Message](src/MimeTypes/Message.php) | `isMessage(string $mimeType): bool` | A type in the `message` category. |
| [Model](src/MimeTypes/Model.php) | `isModel(string $mimeType): bool` | A type in the `model` category. |
| [Multipart](src/MimeTypes/Multipart.php) | `isMultipart(string $mimeType): bool` | A type in the `multipart` category. |
| [Text](src/MimeTypes/Text.php) | `isText(string $mimeType): bool` | A type in the `text` category. |
| [Video](src/MimeTypes/Video.php) | `isVideo(string $mimeType): bool` | A type in the `video` category. |

### Curated subsets

All methods below are static, take no arguments, and return an ordered list of cases of the respective enum (`list<self>`). These curated lists are not exhaustive and do not establish security, quality, or format compatibility.

#### [`Application`](src/MimeTypes/Application.php)

| Signature | Purpose |
| --- | --- |
| `archives(): array` | Common archive types. |
| `documents(): array` | Common document, spreadsheet and presentation types. |
| `data(): array` | Selected data serialization and interchange types. |

#### [`Audio`](src/MimeTypes/Audio.php)

| Signature | Purpose |
| --- | --- |
| `common(): array` | Common audio types. |
| `lossless(): array` | Types associated with formats commonly used for lossless audio; the MIME type alone does not guarantee lossless encoding. |
| `commonWebFormats(): array` | Common web-oriented audio formats. |

#### [`Font`](src/MimeTypes/Font.php)

| Signature | Purpose |
| --- | --- |
| `commonWebFormats(): array` | Common web-oriented font formats. |
| `modern(): array` | WOFF2 and WOFF, in that order. |

#### [`Image`](src/MimeTypes/Image.php)

| Signature | Purpose |
| --- | --- |
| `common(): array` | Common image types. |
| `commonWebFormats(): array` | Common web-oriented image formats. |

#### [`Message`](src/MimeTypes/Message.php)

| Signature | Purpose |
| --- | --- |
| `email(): array` | Selected email and email notification types. |

#### [`Model`](src/MimeTypes/Model.php)

| Signature | Purpose |
| --- | --- |
| `common(): array` | Common 3D model formats. |
| `commonWebFormats(): array` | Selected formats for web-oriented 3D applications. |
| `printing3d(): array` | Selected types used in 3D printing workflows. |

#### [`Multipart`](src/MimeTypes/Multipart.php)

| Signature | Purpose |
| --- | --- |
| `common(): array` | Common multipart message types. |

#### [`Text`](src/MimeTypes/Text.php)

| Signature | Purpose |
| --- | --- |
| `programming(): array` | Selected programming-language source types. |
| `web(): array` | Selected web-related text types. |
| `data(): array` | Selected textual data formats. |
| `markup(): array` | Selected markup types. |

#### [`Video`](src/MimeTypes/Video.php)

| Signature | Purpose |
| --- | --- |
| `common(): array` | Common video types. |
| `commonWebFormats(): array` | Common web-oriented video formats. |
| `streaming(): array` | Selected streaming-related types. |
| `highQuality(): array` | Selected types associated with higher-quality video; the label does not establish actual quality. |


## [`ExtensionResolverInterface`](src/ExtensionResolverInterface.php)

The narrow contract for providing a preferred extension independently of a full MIME catalog. `MimeMapInterface` extends this contract.

#### `getExtension()`

`getExtension(string $mimeType): ?string`

Resolves a normalized preferred extension without a leading dot. The MIME type is a concrete type; parameters are ignored.

| Argument | Description |
| --- | --- |
| `$mimeType` | Concrete MIME type to look up. |

**Returns:** Extension, or `null` for invalid or unmapped types.

**Exceptions:** `DetectorExceptionInterface` if an external mapping backend fails. Unexpected defects propagate unchanged.

## Dependency injection

`ConfigProvider` registers `MimeMap` as the default `MimeMapInterface` and `FinfoDetector` through a factory. All four contracts and `DetectorInterface` resolve to the same service according to container lifetime policy. The default `ExtensionResolverInterface` alias points to `MimeMapInterface`, so overriding the map still affects detection. Override `ExtensionResolverInterface` directly when only preferred-extension lookup is required. A custom resolver must report expected lookup failures through `DetectorExceptionInterface`; other exceptions propagate unchanged.

```php
use Componenta\Detector\MimeTypeDetectorInterface;

$detector = $container->get(MimeTypeDetectorInterface::class);
```

## Security

MIME types and extensions do not prove file safety, authenticity or full format validity. Validate content independently, use allowlists, size limits, appropriate response headers, isolation and storage policy. HTML, JavaScript, SVG and other formats can contain active content.
