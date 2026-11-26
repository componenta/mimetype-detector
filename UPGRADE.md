# Upgrading to 2.x

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
