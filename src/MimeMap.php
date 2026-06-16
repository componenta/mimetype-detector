<?php

declare(strict_types=1);

namespace Componenta\Detector;

class MimeMap implements MimeMapInterface
{
    /**
     * MIME type => extensions mapping
     * @var array<string, list<string>>
     */
    private array $mimeToExt;

    /**
     * Extension => MIME type mapping (lazy-built)
     * @var array<string, string>|null
     */
    private ?array $extToMime = null;

    public function __construct()
    {
        $this->mimeToExt = self::getDefaultMap();
    }

    /**
     * Extend the map with additional MIME types
     *
     * @param array<string, list<string>> $map MIME type => extensions mapping
     * @param bool $override Whether to override existing entries
     */
    public function extend(array $map, bool $override = false): static
    {
        foreach ($map as $mimeType => $extensions) {
            $mimeType = strtolower($mimeType);
            $extensions = array_map('strtolower', $extensions);

            if ($override || !isset($this->mimeToExt[$mimeType])) {
                $this->mimeToExt[$mimeType] = $extensions;
            } else {
                $this->mimeToExt[$mimeType] = array_values(
                    array_unique([...$this->mimeToExt[$mimeType], ...$extensions])
                );
            }
        }

        // Reset lazy-built reverse map
        $this->extToMime = null;

        return $this;
    }

    /**
     * Remove MIME types from the map
     *
     * @param string ...$mimeTypes
     */
    public function remove(string ...$mimeTypes): static
    {
        foreach ($mimeTypes as $mimeType) {
            unset($this->mimeToExt[strtolower($mimeType)]);
        }

        $this->extToMime = null;

        return $this;
    }

    /**
     * Replace the entire map
     *
     * @param array<string, list<string>> $map
     */
    public function setMap(array $map): static
    {
        $this->mimeToExt = [];

        foreach ($map as $mimeType => $extensions) {
            $this->mimeToExt[strtolower($mimeType)] = array_map('strtolower', $extensions);
        }

        $this->extToMime = null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExtensions(string $mimeType): array
    {
        return $this->mimeToExt[strtolower($mimeType)] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getExtension(string $mimeType): ?string
    {
        $extensions = $this->getExtensions($mimeType);

        return $extensions[0] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getMimeType(string $extension): ?string
    {
        $extension = strtolower(ltrim($extension, '.'));

        return $this->getExtToMimeMap()[$extension] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getMimeTypes(string $extension): array
    {
        $extension = strtolower(ltrim($extension, '.'));
        $mimeTypes = [];

        foreach ($this->mimeToExt as $mime => $extensions) {
            if (in_array($extension, $extensions, true)) {
                $mimeTypes[] = $mime;
            }
        }

        return $mimeTypes;
    }

    /**
     * @inheritDoc
     */
    public function isValidExtension(string $mimeType, string $extension): bool
    {
        $extension = strtolower(ltrim($extension, '.'));

        return in_array($extension, $this->getExtensions($mimeType), true);
    }

    /**
     * @inheritDoc
     */
    public function hasMimeType(string $mimeType): bool
    {
        return isset($this->mimeToExt[strtolower($mimeType)]);
    }

    /**
     * @inheritDoc
     */
    public function hasExtension(string $extension): bool
    {
        $extension = strtolower(ltrim($extension, '.'));

        return isset($this->getExtToMimeMap()[$extension]);
    }

    /**
     * @inheritDoc
     */
    public function getAllMimeTypes(): array
    {
        return array_keys($this->mimeToExt);
    }

    /**
     * @inheritDoc
     */
    public function getAllExtensions(): array
    {
        return array_keys($this->getExtToMimeMap());
    }

    /**
     * @inheritDoc
     */
    public function getMimeTypesByCategory(string $category): array
    {
        $category = strtolower($category) . '/';
        $result = [];

        foreach ($this->mimeToExt as $mime => $extensions) {
            if (str_starts_with($mime, $category)) {
                $result[$mime] = $extensions;
            }
        }

        return $result;
    }

    /**
     * Build extension to MIME map (lazy-loaded)
     *
     * @return array<string, string>
     */
    private function getExtToMimeMap(): array
    {
        if ($this->extToMime === null) {
            $this->extToMime = [];

            foreach ($this->mimeToExt as $mime => $extensions) {
                foreach ($extensions as $ext) {
                    // First MIME type wins for each extension
                    if (!isset($this->extToMime[$ext])) {
                        $this->extToMime[$ext] = $mime;
                    }
                }
            }
        }

        return $this->extToMime;
    }

    /**
     * Get the default MIME type map
     *
     * Based on:
     * - IANA Media Types Registry (https://www.iana.org/assignments/media-types/)
     * - RFC 6838 (Media Type Specifications and Registration Procedures)
     * - RFC 4855 (Media Type Registration of RTP Payload Formats)
     * - RFC 7231 (HTTP/1.1 Semantics and Content)
     * - Apache HTTPD mime.types
     * - nginx mime.types
     *
     * @return array<string, list<string>>
     */
    public static function getDefaultMap(): array
    {
        return [
            // =============================================================
            // IMAGE (RFC 6838, IANA Image Media Types)
            // =============================================================

            // Modern formats
            'image/avif' => ['avif'],                    // IANA registered
            'image/webp' => ['webp'],                    // IANA registered
            'image/jxl' => ['jxl'],                      // JPEG XL, IANA registered
            'image/apng' => ['apng'],                    // Animated PNG

            // JPEG family (RFC 2045, RFC 2046)
            'image/jpeg' => ['jpg', 'jpeg', 'jpe'],      // IANA registered, jfif is not standard
            'image/jp2' => ['jp2', 'jpg2'],              // JPEG 2000
            'image/jpx' => ['jpx', 'jpf'],               // JPEG 2000 Part 2
            'image/jpm' => ['jpm'],                      // JPEG 2000 Part 6
            'image/jxr' => ['jxr'],                      // JPEG XR
            'image/jph' => ['jph'],                      // High-Throughput JPEG 2000
            'image/jxs' => ['jxs'],                      // JPEG XS

            // PNG family
            'image/png' => ['png'],                      // RFC 2083

            // GIF
            'image/gif' => ['gif'],                      // RFC 2045, RFC 2046

            // TIFF
            'image/tiff' => ['tiff', 'tif'],             // RFC 3302

            // BMP
            'image/bmp' => ['bmp', 'dib'],               // IANA registered

            // HEIF/HEIC (ISO/IEC 23008-12)
            'image/heif' => ['heif', 'hif'],             // IANA registered
            'image/heic' => ['heic'],                    // IANA registered
            'image/heif-sequence' => ['heifs'],          // IANA registered
            'image/heic-sequence' => ['heics'],          // IANA registered

            // SVG (W3C)
            'image/svg+xml' => ['svg', 'svgz'],          // IANA registered

            // Icons
            'image/vnd.microsoft.icon' => ['ico'],       // IANA registered (preferred over x-icon)

            // Adobe
            'image/vnd.adobe.photoshop' => ['psd'],      // IANA registered

            // Other registered types
            'image/vnd.djvu' => ['djvu', 'djv'],
            'image/vnd.dwg' => [],                         // Use application/vnd.autocad.dwg
            'image/vnd.dxf' => ['dxf'],
            'image/vnd.wap.wbmp' => ['wbmp'],
            'image/vnd.radiance' => ['hdr', 'rgbe'],

            // X-types (legacy but widely used)
            'image/x-icon' => ['cur'],                     // Legacy, ico should use vnd.microsoft.icon
            'image/x-ms-bmp' => [],                        // Legacy, use image/bmp
            'image/x-tga' => ['tga', 'icb', 'vda'],        // .vst used by Visio
            'image/x-pcx' => ['pcx'],
            'image/x-portable-anymap' => ['pnm'],
            'image/x-portable-bitmap' => ['pbm'],
            'image/x-portable-graymap' => ['pgm'],
            'image/x-portable-pixmap' => ['ppm'],
            'image/x-rgb' => ['rgb'],
            'image/x-xbitmap' => ['xbm'],
            'image/x-xpixmap' => ['xpm'],
            'image/x-xwindowdump' => ['xwd'],
            'image/x-cmu-raster' => ['ras'],
            'image/x-freehand' => ['fh', 'fhc', 'fh4', 'fh5', 'fh7'],

            // EMF/WMF (Windows Metafile)
            'image/emf' => ['emf'],                      // IANA registered
            'image/wmf' => ['wmf'],                      // IANA registered

            // KTX (Khronos Texture)
            'image/ktx' => ['ktx'],
            'image/ktx2' => ['ktx2'],

            // =============================================================
            // VIDEO (RFC 6838, IANA Video Media Types)
            // =============================================================

            // MPEG family
            'video/mpeg' => ['mpeg', 'mpg', 'mpe', 'm1v', 'm2v'],  // RFC 2045, RFC 2046
            'video/mp4' => ['mp4', 'm4v', 'mp4v'],                  // RFC 4337
            'video/mp2t' => ['mts', 'm2ts'],                 // MPEG-2 Transport Stream, .ts conflicts with TypeScript

            // WebM/VP8/VP9
            'video/webm' => ['webm'],                    // IANA registered

            // Ogg
            'video/ogg' => ['ogv'],                        // RFC 5334

            // QuickTime
            'video/quicktime' => ['mov', 'qt'],          // IANA registered

            // AVI
            'video/x-msvideo' => ['avi'],                // De facto standard

            // Matroska
            'video/x-matroska' => ['mkv', 'mk3d', 'mks'],

            // Windows Media
            'video/x-ms-wmv' => ['wmv'],
            'video/x-ms-asf' => ['asf', 'asx'],
            'video/x-ms-wm' => ['wm'],
            'video/x-ms-wmx' => ['wmx'],
            'video/x-ms-wvx' => ['wvx'],

            // Flash Video
            'video/x-flv' => ['flv'],
            'video/x-f4v' => ['f4v'],

            // 3GPP (RFC 3839, RFC 4393)
            'video/3gpp' => ['3gp', '3gpp'],
            'video/3gpp2' => ['3g2', '3gpp2'],

            // Raw video codecs (RFC 4855, RFC 6184, RFC 7798)
            'video/h264' => ['h264', '264'],             // RFC 6184 (lowercase per IANA)
            'video/h265' => ['h265', '265', 'hevc'],     // RFC 7798
            'video/h266' => ['h266', 'vvc'],             // VVC
            'video/av1' => ['av1'],                      // AV1
            'video/vp8' => ['vp8'],                      // RFC 7741
            'video/vp9' => ['vp9'],                      // RFC 8656

            // Other
            'video/jpeg' => ['jpgv'],                    // Motion JPEG
            'video/mj2' => ['mj2', 'mjp2'],              // Motion JPEG 2000

            // Vendor types
            'video/vnd.ms-playready.media.pyv' => ['pyv'],
            'video/vnd.dece.hd' => ['uvh', 'uvvh'],
            'video/vnd.dece.mobile' => ['uvm', 'uvvm'],
            'video/vnd.dece.pd' => ['uvp', 'uvvp'],
            'video/vnd.dece.sd' => ['uvs', 'uvvs'],
            'video/vnd.dece.video' => ['uvv', 'uvvv'],
            'video/vnd.dvb.file' => ['dvb'],
            'video/vnd.fvt' => ['fvt'],
            'video/vnd.mpegurl' => ['mxu', 'm4u'],
            'video/vnd.radgamettools.bink' => ['bik', 'bk2'],
            'video/vnd.radgamettools.smacker' => ['smk'],
            'video/vnd.vivo' => ['viv'],

            // =============================================================
            // AUDIO (RFC 6838, IANA Audio Media Types)
            // =============================================================

            // MPEG Audio (RFC 3003)
            'audio/mpeg' => ['mp3', 'mp2', 'mp1', 'mpga'],  // RFC 3003, NOT audio/mp3

            // AAC
            'audio/aac' => ['aac', 'adts'],              // IANA registered

            // MP4 Audio
            'audio/mp4' => ['m4a', 'mp4a'],              // RFC 4337

            // Ogg (RFC 5334)
            'audio/ogg' => ['oga', 'ogg', 'spx'],          // RFC 5334, ogg typically audio
            'audio/opus' => ['opus'],                       // RFC 7587
            'audio/vorbis' => [],                           // RFC 5215, codec not container

            // WAV
            'audio/wav' => ['wav'],                       // IANA registered
            'audio/x-wav' => [],                          // Legacy, use audio/wav

            // WebM Audio
            'audio/webm' => ['weba'],

            // FLAC
            'audio/flac' => ['flac'],                     // IANA registered
            'audio/x-flac' => [],                         // Legacy, use audio/flac

            // AIFF
            'audio/aiff' => ['aif', 'aiff', 'aifc'],      // IANA registered
            'audio/x-aiff' => [],                         // Legacy, use audio/aiff

            // AC-3 / E-AC-3 (Dolby)
            'audio/ac3' => ['ac3'],                       // RFC 4184
            'audio/eac3' => ['eac3', 'ec3'],              // RFC 4598

            // DTS
            'audio/vnd.dts' => ['dts'],
            'audio/vnd.dts.hd' => ['dtshd'],

            // MIDI
            'audio/midi' => ['mid', 'midi', 'kar', 'rmi'], // IANA registered
            'audio/x-midi' => [],                          // Legacy, use audio/midi

            // Basic (RFC 2045, RFC 2046)
            'audio/basic' => ['au', 'snd'],

            // AMR (RFC 4867)
            'audio/amr' => ['amr'],
            'audio/amr-wb' => ['awb'],

            // 3GPP Audio
            'audio/3gpp' => [],                            // Use video/3gpp for .3gp
            'audio/3gpp2' => [],                           // Use video/3gpp2 for .3g2

            // Tracker modules (x- types, not IANA registered)
            'audio/x-xm' => ['xm'],                        // FastTracker 2
            'audio/x-mod' => ['mod'],                      // Amiga MOD
            'audio/x-s3m' => ['s3m'],                      // Scream Tracker 3
            'audio/x-it' => ['it'],                        // Impulse Tracker

            // Windows Media
            'audio/x-ms-wma' => ['wma'],

            // RealAudio
            'audio/vnd.rn-realaudio' => ['ra', 'ram'],
            'audio/x-pn-realaudio' => [],                  // Legacy, use vnd.rn-realaudio

            // Other
            'audio/x-m4a' => [],                          // Legacy, use audio/mp4
            'audio/x-matroska' => ['mka'],
            'audio/x-ape' => ['ape'],                     // Monkey's Audio
            'audio/x-wavpack' => ['wv', 'wvp'],
            'audio/x-tta' => ['tta'],                     // True Audio
            'audio/x-musepack' => ['mpc', 'mp+'],          // .mpp used by MS Project
            'audio/x-caf' => ['caf'],                     // Core Audio Format
            'audio/x-gsm' => ['gsm'],

            // Playlists
            'audio/x-mpegurl' => ['m3u', 'm3u8'],
            'audio/x-scpls' => ['pls'],

            // DSD Audio
            'audio/x-dsf' => ['dsf'],
            'audio/x-dff' => ['dff'],

            // =============================================================
            // APPLICATION (RFC 6838, IANA Application Media Types)
            // =============================================================

            // JSON family (RFC 8259)
            'application/json' => ['json', 'map'],        // RFC 8259
            'application/ld+json' => ['jsonld'],          // JSON-LD
            'application/json-patch+json' => ['json-patch'],
            'application/json-seq' => ['json-seq'],
            'application/jwk+json' => ['jwk'],
            'application/jwk-set+json' => ['jwks'],
            'application/jwt' => ['jwt'],                 // RFC 7519
            'application/schema+json' => [],               // JSON Schema, no file extension
            'application/geo+json' => ['geojson'],        // RFC 7946
            'application/hal+json' => ['hal'],
            'application/graphql+json' => [],              // HTTP content type for GraphQL queries
            'application/vnd.api+json' => [],             // JSON:API
            'application/problem+json' => [],             // RFC 7807
            'application/merge-patch+json' => [],         // RFC 7396
            'application/activity+json' => [],            // ActivityPub
            'application/scim+json' => [],                // SCIM

            // JavaScript - OBSOLETE per RFC 9239, use text/javascript instead
            // Extensions removed to ensure text/javascript wins in reverse mapping
            'application/javascript' => [],               // RFC 9239: OBSOLETE
            'application/ecmascript' => [],               // RFC 9239: OBSOLETE

            // XML family
            'application/xml' => ['xml', 'xsl', 'xsd', 'rng'],  // RFC 7303
            'application/xml-dtd' => ['dtd'],
            'application/xhtml+xml' => ['xhtml', 'xht'],
            'application/xslt+xml' => ['xslt'],
            'application/mathml+xml' => ['mathml', 'mml'],
            'application/rss+xml' => ['rss'],
            'application/atom+xml' => ['atom'],
            'application/soap+xml' => ['soap'],            // RFC 3902
            'application/wsdl+xml' => ['wsdl'],
            'application/gpx+xml' => ['gpx'],
            'application/gml+xml' => ['gml'],
            'application/smil+xml' => ['smi', 'smil'],
            'application/xspf+xml' => ['xspf'],
            'application/dash+xml' => ['mpd'],             // MPEG-DASH
            'application/problem+xml' => [],               // RFC 7807

            // PDF (RFC 3778)
            'application/pdf' => ['pdf'],

            // Binary stream
            'application/octet-stream' => ['bin', 'dms', 'lrf', 'mar', 'dist', 'distz', 'bpk', 'dump', 'elc', 'deploy', 'com'],

            // Archive formats
            'application/zip' => ['zip'],                  // RFC 6713
            'application/gzip' => ['gz', 'gzip'],          // RFC 6713
            'application/x-gzip' => [],                    // Legacy, use application/gzip
            'application/x-bzip' => ['bz'],
            'application/x-bzip2' => ['bz2', 'boz'],
            'application/x-tar' => ['tar'],
            'application/vnd.rar' => ['rar'],              // IANA registered
            'application/x-7z-compressed' => ['7z'],
            'application/x-xz' => ['xz'],
            'application/zstd' => ['zst', 'zstd'],         // RFC 8878
            'application/x-lz4' => ['lz4'],
            'application/x-lzip' => ['lz'],
            'application/x-lzma' => ['lzma'],
            'application/x-lzop' => ['lzo'],
            'application/x-compress' => ['z'],             // Unix compress (.Z files)
            'application/x-cpio' => ['cpio'],
            'application/x-shar' => ['shar'],
            'application/x-stuffit' => ['sit'],
            'application/x-stuffitx' => ['sitx'],
            'application/x-gtar' => ['gtar'],
            'application/x-ustar' => ['ustar'],
            'application/x-arj' => ['arj'],
            'application/x-ace-compressed' => ['ace'],
            'application/x-lzh-compressed' => ['lzh', 'lha'],
            'application/vnd.ms-cab-compressed' => ['cab'],

            // Disk images
            'application/x-iso9660-image' => ['iso'],
            'application/x-apple-diskimage' => ['dmg'],

            // Package formats
            'application/vnd.debian.binary-package' => ['deb', 'udeb'],
            'application/x-rpm' => ['rpm'],
            'application/x-redhat-package-manager' => [],  // Alias for x-rpm
            'application/vnd.android.package-archive' => ['apk'],
            'application/x-msi' => ['msi'],
            'application/x-xar' => ['xar', 'pkg'],

            // RTF
            'application/rtf' => ['rtf'],                  // RFC 2045, RFC 2046

            // PostScript
            'application/postscript' => ['ai', 'eps', 'ps'],

            // Microsoft Office (Legacy)
            'application/msword' => ['doc'],               // .dot used by Graphviz
            'application/vnd.ms-excel' => ['xls', 'xlm', 'xla', 'xlc', 'xlt', 'xlw'],
            'application/vnd.ms-powerpoint' => ['ppt', 'pps', 'pot'],
            'application/vnd.ms-access' => ['mdb', 'accdb'],
            'application/vnd.ms-project' => ['mpp', 'mpt'],
            'application/vnd.visio' => ['vsd', 'vst', 'vss', 'vsw'],
            'application/vnd.ms-publisher' => ['pub'],
            'application/vnd.ms-outlook' => ['msg'],

            // Microsoft Office (OOXML) - ECMA-376
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template' => ['dotx'],
            'application/vnd.ms-word.document.macroenabled.12' => ['docm'],
            'application/vnd.ms-word.template.macroenabled.12' => ['dotm'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template' => ['xltx'],
            'application/vnd.ms-excel.addin.macroenabled.12' => ['xlam'],
            'application/vnd.ms-excel.sheet.macroenabled.12' => ['xlsm'],
            'application/vnd.ms-excel.template.macroenabled.12' => ['xltm'],
            'application/vnd.ms-excel.sheet.binary.macroenabled.12' => ['xlsb'],
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
            'application/vnd.openxmlformats-officedocument.presentationml.template' => ['potx'],
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => ['ppsx'],
            'application/vnd.ms-powerpoint.presentation.macroenabled.12' => ['pptm'],
            'application/vnd.ms-powerpoint.template.macroenabled.12' => ['potm'],
            'application/vnd.ms-powerpoint.slideshow.macroenabled.12' => ['ppsm'],
            'application/vnd.ms-powerpoint.addin.macroenabled.12' => ['ppam'],

            // OpenDocument (OASIS)
            'application/vnd.oasis.opendocument.text' => ['odt'],
            'application/vnd.oasis.opendocument.text-template' => ['ott'],
            'application/vnd.oasis.opendocument.spreadsheet' => ['ods'],
            'application/vnd.oasis.opendocument.spreadsheet-template' => ['ots'],
            'application/vnd.oasis.opendocument.presentation' => ['odp'],
            'application/vnd.oasis.opendocument.presentation-template' => ['otp'],
            'application/vnd.oasis.opendocument.graphics' => ['odg'],
            'application/vnd.oasis.opendocument.graphics-template' => ['otg'],
            'application/vnd.oasis.opendocument.chart' => ['odc'],
            'application/vnd.oasis.opendocument.formula' => ['odf'],
            'application/vnd.oasis.opendocument.database' => ['odb'],
            'application/vnd.oasis.opendocument.image' => ['odi'],

            // Apple iWork
            'application/vnd.apple.keynote' => ['key'],
            'application/vnd.apple.numbers' => ['numbers'],
            'application/vnd.apple.pages' => ['pages'],

            // eBooks
            'application/epub+zip' => ['epub'],            // IDPF/W3C
            'application/x-mobipocket-ebook' => ['mobi', 'prc'],
            'application/vnd.amazon.ebook' => ['azw', 'azw3'],
            'application/x-fictionbook+xml' => ['fb2'],

            // Fonts (RFC 8081)
            'font/otf' => ['otf'],                         // RFC 8081
            'font/ttf' => ['ttf'],                         // RFC 8081
            'font/woff' => ['woff'],                       // RFC 8081
            'font/woff2' => ['woff2'],                     // RFC 8081
            'font/collection' => ['ttc'],                  // RFC 8081
            'font/sfnt' => [],                             // RFC 8081, container format

            // Legacy font types (kept for compatibility, use font/* instead)
            'application/font-otf' => [],                  // Legacy, use font/otf
            'application/font-ttf' => [],                  // Legacy, use font/ttf
            'application/font-woff' => [],                 // Legacy, use font/woff
            'application/font-woff2' => [],                // Legacy, use font/woff2
            'application/vnd.ms-fontobject' => ['eot'],
            'application/x-font-ttf' => [],                // Legacy, use font/ttf
            'application/x-font-otf' => [],                // Legacy, use font/otf
            'application/x-font-woff' => [],               // Legacy, use font/woff
            'application/x-font-bdf' => ['bdf'],
            'application/x-font-pcf' => ['pcf'],
            'application/x-font-type1' => ['pfa', 'pfb', 'pfm', 'afm'],

            // Java
            'application/java-archive' => ['jar', 'war', 'ear'],
            'application/java-serialized-object' => ['ser'],
            'application/java-vm' => ['class'],

            // WebAssembly
            'application/wasm' => ['wasm'],                // IANA registered

            // Executables - application/vnd.microsoft.portable-executable is IANA registered
            'application/x-executable' => [],              // Generic, use vnd.microsoft.portable-executable for PE
            'application/x-sharedlib' => ['so'],
            'application/x-msdos-program' => [],           // Legacy
            'application/x-msdownload' => [],              // Legacy
            'application/vnd.microsoft.portable-executable' => ['exe', 'dll'],
            'application/x-mach-binary' => ['dylib'],
            'application/x-elf' => ['elf'],

            // Media containers
            'application/ogg' => ['ogx'],                  // RFC 5334, generic Ogg
            'application/mp4' => ['mp4s', 'm4p'],
            'application/mxf' => ['mxf'],                  // RFC 4539

            // Google Earth
            'application/vnd.google-earth.kml+xml' => ['kml'],
            'application/vnd.google-earth.kmz' => ['kmz'],

            // 3D formats - legacy types, prefer model/* equivalents
            'application/gltf+json' => [],                 // Deprecated, use model/gltf+json
            'application/gltf-binary' => [],               // Deprecated, use model/gltf-binary
            'application/sla' => [],                       // Legacy, use model/stl
            'application/vnd.ms-pki.stl' => [],            // Legacy, use model/stl
            'application/step' => [],                      // Not IANA registered, use model/step
            'application/iges' => [],                      // Not IANA registered, use model/iges
            'application/vnd.autocad.dwg' => ['dwg'],

            // Security / Certificates
            'application/pkcs7-mime' => ['p7m', 'p7c'],
            'application/pkcs7-signature' => ['p7s'],
            'application/pkcs8' => ['p8'],
            'application/pkcs10' => ['p10', 'csr'],
            'application/x-pkcs12' => ['p12', 'pfx'],
            'application/pkix-cert' => ['cer'],
            'application/pkix-crl' => ['crl'],
            'application/pkixcmp' => ['pki'],
            'application/x-pem-file' => ['pem'],
            'application/x-pkcs7-certificates' => ['p7b', 'spc'],
            'application/pgp-encrypted' => ['pgp'],
            'application/pgp-keys' => ['asc'],
            'application/pgp-signature' => ['sig'],        // .asc handled by pgp-keys
            'application/x-x509-ca-cert' => ['crt', 'der'],

            // Data formats
            'application/sql' => ['sql'],
            'application/vnd.sqlite3' => ['sqlite', 'sqlite3', 'db'],
            'application/x-sqlite3' => [],                 // Legacy, use vnd.sqlite3
            'application/cbor' => ['cbor'],                // RFC 8949
            'application/cbor-seq' => ['cbor-seq'],
            'application/msgpack' => ['msgpack'],

            // Configuration
            'application/yaml' => ['yaml', 'yml'],         // RFC 9512 (February 2024)
            'application/toml' => ['toml'],                // IANA registered

            // Shells
            'application/x-sh' => ['sh'],
            'application/x-csh' => ['csh'],
            'application/x-httpd-php' => ['php', 'php3', 'php4', 'php5', 'phtml'],

            // TeX/LaTeX
            'application/x-latex' => ['latex'],
            'application/x-tex' => ['tex'],

            // Misc
            'application/x-bittorrent' => ['torrent'],
            'application/x-nzb' => ['nzb'],
            'application/vnd.tcpdump.pcap' => ['pcap', 'cap', 'dmp'],
            'application/x-shockwave-flash' => ['swf'],
            'application/vnd.adobe.fxp' => ['fxp', 'fxpl'],
            'application/x-indesign' => ['indd'],
            'application/vnd.ms-xpsdocument' => ['xps'],

            // EDI
            'application/edi-x12' => ['x12'],              // RFC 1767 (lowercase per RFC)
            'application/edifact' => ['edi'],              // RFC 1767

            // =============================================================
            // TEXT (RFC 6838, IANA Text Media Types)
            // =============================================================

            // Plain text (RFC 2046)
            'text/plain' => ['txt', 'text', 'def', 'list', 'log', 'in'],

            // HTML
            'text/html' => ['html', 'htm', 'shtml'],       // RFC 2854

            // CSS
            'text/css' => ['css'],                         // RFC 2318

            // CSV/TSV
            'text/csv' => ['csv'],                         // RFC 4180
            'text/tab-separated-values' => ['tsv'],

            // JavaScript (RFC 9239 - text/javascript is now standard!)
            'text/javascript' => ['js', 'mjs', 'es'],      // RFC 9239 (preferred)

            // XML
            'text/xml' => [],                              // RFC 7303, use application/xml

            // Markdown
            'text/markdown' => ['md', 'markdown', 'mdown', 'mkd'],  // RFC 7763

            // Other markup
            'text/x-asciidoc' => ['adoc', 'asciidoc'],     // Not IANA registered
            'text/x-rst' => ['rst'],
            'text/x-org' => ['org'],
            'text/x-textile' => ['textile'],
            'text/troff' => ['tr', 'roff', 'man', 'me', 'ms'], // t conflicts with perl
            'text/richtext' => ['rtx'],
            'text/sgml' => ['sgml', 'sgm'],

            // Programming languages
            'text/x-php' => ['inc'],                       // Source code, php extensions use application/x-httpd-php
            'text/x-python' => ['py', 'pyw', 'pyx', 'pxd', 'pxi'],
            'text/x-ruby' => ['rb', 'rbw', 'rake', 'gemspec'],
            'text/x-perl' => ['pl', 'pm', 'pod', 't'],
            'text/x-java-source' => ['java'],
            'text/x-c' => ['c', 'h'],                       // C source/header only
            'text/x-c++src' => ['cpp', 'cxx', 'cc', 'c++'],
            'text/x-c++hdr' => ['hpp', 'hxx', 'hh', 'h++'],
            'text/x-csharp' => ['cs'],
            'text/x-go' => ['go'],
            'text/x-rust' => ['rs'],
            'text/x-swift' => ['swift'],
            'text/x-kotlin' => ['kt', 'kts'],
            'text/x-scala' => ['scala', 'sc'],
            'text/x-groovy' => ['groovy', 'gvy', 'gy', 'gsh'],
            'text/x-clojure' => ['clj', 'cljs', 'cljc', 'edn'],
            'text/x-lua' => ['lua'],
            'text/x-tcl' => ['tcl', 'tk'],
            'text/x-fortran' => ['f', 'for', 'f77', 'f90', 'f95'],
            'text/x-pascal' => ['pas', 'p', 'pp'],
            'text/x-haskell' => ['hs', 'lhs'],
            'text/x-erlang' => ['erl', 'hrl'],
            'text/x-elixir' => ['ex', 'exs'],
            'text/x-ocaml' => ['ml', 'mli'],
            'text/x-fsharp' => ['fs', 'fsi', 'fsx', 'fsscript'],
            'text/x-lisp' => ['lisp', 'lsp', 'cl', 'el'],
            'text/x-scheme' => ['scm', 'ss'],
            'text/x-racket' => ['rkt'],
            'text/x-prolog' => ['pro', 'prolog'],
            'text/x-asm' => ['s', 'asm'],
            'text/x-nasm' => ['nasm'],
            'text/x-nim' => ['nim', 'nims'],
            'text/x-zig' => ['zig'],
            'text/x-julia' => ['jl'],
            'text/x-r' => ['r'],
            'text/x-matlab' => ['m', 'mat'],
            'text/x-objectivec' => ['mm'],                 // .m handled by matlab
            'text/x-dart' => ['dart'],

            // TypeScript/JSX/TSX (not IANA registered)
            'text/x-typescript' => ['ts'],
            'text/x-tsx' => ['tsx'],
            'text/x-jsx' => ['jsx'],
            'text/x-coffeescript' => ['coffee', 'litcoffee'],

            // Shell scripts
            'text/x-shellscript' => [],                    // Freedesktop, use application/x-sh
            'text/x-sh' => [],                             // Legacy, use application/x-sh
            'text/x-bash' => ['bash'],
            'text/x-zsh' => ['zsh'],
            'text/x-fish' => ['fish'],
            'text/x-powershell' => ['ps1', 'psm1', 'psd1'],
            'text/x-bat' => ['bat', 'cmd'],

            // Build systems
            'text/x-makefile' => ['mak', 'mk', 'makefile'],
            'text/x-cmake' => ['cmake'],
            'text/x-dockerfile' => ['dockerfile'],

            // Config files
            'text/x-yaml' => [],                           // Legacy, use application/yaml
            'text/x-toml' => [],                           // Use application/toml
            'text/x-properties' => ['properties'],
            'text/x-ini' => ['ini', 'cfg'],
            'text/x-dotenv' => ['env'],
            'text/x-nginx-conf' => ['conf'],
            'text/x-apache-conf' => ['htaccess'],          // conf handled by nginx-conf
            'text/x-systemd-unit' => ['service', 'socket', 'device', 'mount', 'automount', 'swap', 'target', 'path', 'timer', 'slice', 'scope'],
            'text/x-editorconfig' => ['editorconfig'],
            'text/x-gitignore' => ['gitignore'],
            'text/x-gitattributes' => ['gitattributes'],

            // Cycle/Query
            'text/x-sql' => [],                            // Legacy, use application/sql
            'text/x-graphql' => ['graphql', 'gql'],

            // Templates
            'text/x-twig' => ['twig'],
            'text/x-blade' => ['blade.php'],
            'text/x-jinja' => ['j2', 'jinja', 'jinja2'],
            'text/x-mustache' => ['mustache'],
            'text/x-handlebars-template' => ['hbs', 'handlebars'],
            'text/x-ejs' => ['ejs'],
            'text/x-pug' => ['pug', 'jade'],
            'text/x-haml' => ['haml'],

            // Other text types
            'text/vcard' => ['vcf', 'vcard'],              // RFC 6350
            'text/calendar' => ['ics', 'ifb'],             // RFC 5545
            'text/x-vcalendar' => ['vcs'],
            'text/vnd.wap.wml' => ['wml'],
            'text/vnd.wap.wmlscript' => ['wmls'],
            'text/vnd.graphviz' => ['gv', 'dot'],
            'text/cache-manifest' => ['appcache', 'manifest'],
            'text/event-stream' => [],                     // Server-Sent Events
            'text/n3' => ['n3'],                           // Notation3
            'text/turtle' => ['ttl'],                      // RDF Turtle
            'text/vtt' => ['vtt'],                         // WebVTT, IANA registered (W3C)
            'text/uri-list' => ['uri', 'uris', 'urls'],    // RFC 2483
            'text/x-nfo' => ['nfo'],
            'text/x-sfv' => ['sfv'],
            'text/x-diff' => ['diff', 'patch'],

            // =============================================================
            // MESSAGE (RFC 2045, RFC 2046)
            // =============================================================
            'message/rfc822' => ['eml', 'mime', 'mht', 'mhtml'],  // RFC 2822
            'message/partial' => [],
            'message/delivery-status' => [],
            'message/disposition-notification' => [],
            'message/http' => [],                          // RFC 7230

            // =============================================================
            // MULTIPART (RFC 2045, RFC 2046)
            // =============================================================
            'multipart/alternative' => [],
            'multipart/form-data' => [],                   // RFC 7578
            'multipart/mixed' => [],
            'multipart/related' => [],                     // RFC 2387
            'multipart/byteranges' => [],                  // RFC 7233

            // =============================================================
            // HAPTICS (RFC 9695, March 2025)
            // =============================================================
            'haptics/ivs' => ['ivs'],                      // Interoperable Vibrotactile Sequence
            'haptics/hjif' => ['hjif'],                    // Haptic JSON Interchange Format
            'haptics/hmpg' => ['hmpg'],                    // MPEG Haptics

            // =============================================================
            // MODEL (IANA Model Media Types)
            // =============================================================
            'model/gltf+json' => ['gltf'],
            'model/gltf-binary' => ['glb'],
            'model/obj' => ['obj'],
            'model/mtl' => ['mtl'],
            'model/stl' => ['stl'],
            'model/step' => ['step', 'stp', 'p21'],
            'model/step+xml' => ['stpx'],
            'model/step+zip' => ['stpz'],
            'model/iges' => ['iges', 'igs'],
            'model/vrml' => ['wrl', 'vrml'],               // RFC 2077
            'model/x3d+vrml' => ['x3dv', 'x3dvz'],
            'model/x3d+xml' => ['x3d', 'x3dz'],
            'model/x3d+binary' => ['x3db', 'x3dbz'],
            'model/x3d+fastinfoset' => [],                 // Uses x3db extension
            'model/x3d-vrml' => [],                        // Uses x3dv extension
            'model/vnd.usdz+zip' => ['usdz'],
            'model/vnd.dwf' => ['dwf'],
            'model/vnd.gdl' => ['gdl'],
            'model/vnd.gtw' => ['gtw'],
            'model/vnd.mts' => [],                         // .mts used by video/mp2t
            'model/vnd.parasolid.transmit.binary' => ['x_b'],
            'model/vnd.parasolid.transmit.text' => ['x_t'],
            'model/vnd.vtu' => ['vtu'],
            'model/mesh' => ['msh', 'mesh', 'silo'],
            'model/prc' => [],                             // .prc used by Mobipocket ebook
            'model/u3d' => ['u3d'],
            'model/3mf' => ['3mf'],
            'model/e57' => ['e57'],
        ];
    }
}