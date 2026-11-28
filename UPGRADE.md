# Upgrading to 3.x

This major version reorganizes the exception and factory namespaces and separates detection failures from source I/O failures.

| 2.x | 3.x |
| --- | --- |
| `Componenta\Detector\DetectorException` | `Componenta\Detector\Exception\DetectorException` |
| `FileNotFoundException` | `Exception\FileUnavailableException` (missing or inaccessible path) |
| `FileNotReadableException` | `Exception\InvalidFileException` (invalid path/type) or `Exception\FileReadException` (I/O failure) |
| `InvalidMimeTypeException` | `Exception\InvalidMimeTypeException` |
| `StreamPositionRestoreException` | `Exception\StreamPositionRestoreException` |
| `FinfoDetectorFactory` | `Factory\FinfoDetectorFactory` |

Names in the table are relative to `Componenta\Detector` unless fully qualified. Update imports and exception catches. The old exception types are not exported as aliases.

- Catch detection failures through `Exception\DetectorExceptionInterface`, file failures through `Exception\FileExceptionInterface`, and stream failures through `Exception\StreamExceptionInterface`. The three categories are independent. A single catch of the old `DetectorException` no longer covers source I/O.
- Stream restoration failures implement `Exception\StreamPositionRestoreExceptionInterface`; `getPrevious()` retains the restoration cause and `readFailure` may be any earlier throwable.
- Invalid detector configuration is reported as `Exception\DetectorConfigurationException` (an `InvalidArgumentException`). Invalid MIME types remain argument errors.
- Detector method names, parameters, nullable value results, map APIs and DI service identifiers are unchanged. Local file methods continue to read at most the configured prefix. Symbolic links to regular files are allowed; non-local paths and special files are rejected.
- Programming errors in injected maps and streams are not blanket-converted into ordinary detector failures. Custom MIME maps must implement `Exception\DetectorExceptionInterface` on expected lookup failures; undeclared exceptions propagate unchanged.
- Consumers pinned to `^2.0` must explicitly migrate to `^3.0` before installing this version. In particular, callers that catch the old detector exception (including image converter/compressor) must update their exception translation. No changes to those packages are included in this branch.

---

## Upgrading to 2.x

This version changes the public contracts.

| Before | After |
| --- | --- |
| detectMimeType($content, asObject: true) or string result | detectMimeType($content) returns ?MimeType; use $mime?->value for a string |
| detectExtension($content, asObject: true) or string result | detectExtension($content) returns ?Ext; use $ext?->value |
| File detection with asObject | File detection always returns nullable value objects |
| $mime->equals('image/*') | $mime->matches('image/*') |
| MimeType::tryFrom(), tryFromFile(), Ext::tryFrom(), tryFromFile() | Use a detector contract |
| MimeType::fromExtension(), toExt(), toAllExt(), Ext::fromMimeType(), allFromMimeType(), toMimeType(), toAllMimeTypes() | Use MimeMapInterface and construct values as needed |
| Ext category methods | Consult the MIME map and apply MIME type categories explicitly |
| MimeType::isWebSafe(), enum webSafe() | Removed security claim; commonWebFormats() is a non-security enum subset |
| MimeType::isBinary(), isCompressible(), contains() | Make content-specific decisions in the application |

Custom implementations of the four detector interfaces must update their return types and remove the boolean mode parameter. Implementations of MimeMapInterface still use string lookup keys and values. MimeType and Ext reject malformed values; code that consumes untrusted data may use tryCreate().

Extension detection now uses the injected map as its sole source and returns its primary extension independent of the file name. Strings, files and streams use an identical bounded prefix. Review any caller that depends on the old fileinfo extension precedence or on complete raw string analysis.

Enum backed values are normalized to lowercase. Previously duplicated case names are constants referring to their canonical cases, so they are not separate entries in cases(). Serialized enum case names and stored media type strings may need migration. The package now requires Componenta Config 4.x.

The detector does not guarantee file safety or exact identification. File paths must be authorized by the application. Non-seekable stream reads consume the prefix.
