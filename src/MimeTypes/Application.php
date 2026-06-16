<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Application: string
{
    // Common
    case Json = 'application/json';
    case JsonLd = 'application/ld+json';
    case JsonPatch = 'application/json-patch+json';
    case JsonSeq = 'application/json-seq';
    case Jwt = 'application/jwt';
    case Javascript = 'application/javascript';
    case EcmaScript = 'application/ecmascript';
    case OctetStream = 'application/octet-stream';
    case Pdf = 'application/pdf';
    case Xml = 'application/xml';
    case XmlDtd = 'application/xml-dtd';
    case XmlExternal = 'application/xml-external-parsed-entity';
    case Xhtml = 'application/xhtml+xml';
    case Zip = 'application/zip';
    case Gzip = 'application/gzip';
    case XGzip = 'application/x-gzip';
    case Bzip = 'application/x-bzip';
    case Bzip2 = 'application/x-bzip2';
    case Tar = 'application/x-tar';
    case Rar = 'application/vnd.rar';
    case X7zCompressed = 'application/x-7z-compressed';
    case XZstd = 'application/x-zstd';
    case Zstd = 'application/zstd';
    
    // Documents
    case Rtf = 'application/rtf';
    case Postscript = 'application/postscript';
    case Latex = 'application/x-latex';
    case Tex = 'application/x-tex';
    
    // Microsoft Office
    case Msword = 'application/msword';
    case MswordX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    case MswordTemplate = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
    case MsExcel = 'application/vnd.ms-excel';
    case MsExcelX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    case MsExcelTemplate = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
    case MsExcelAddin = 'application/vnd.ms-excel.addin.macroEnabled.12';
    case MsExcelSheetMacro = 'application/vnd.ms-excel.sheet.macroEnabled.12';
    case MsExcelSheetBinary = 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
    case MsPowerpoint = 'application/vnd.ms-powerpoint';
    case MsPowerpointX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    case MsPowerpointTemplate = 'application/vnd.openxmlformats-officedocument.presentationml.template';
    case MsPowerpointSlideshow = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
    case MsAccess = 'application/vnd.ms-access';
    case MsProject = 'application/vnd.ms-project';
    case MsVisio = 'application/vnd.visio';
    case MsPublisher = 'application/vnd.ms-publisher';
    case MsOutlook = 'application/vnd.ms-outlook';
    case MsFontObject = 'application/vnd.ms-fontobject';
    case MsXpsdocument = 'application/vnd.ms-xpsdocument';
    
    // OpenDocument
    case OdtText = 'application/vnd.oasis.opendocument.text';
    case OdtTextTemplate = 'application/vnd.oasis.opendocument.text-template';
    case OdsSpreadsheet = 'application/vnd.oasis.opendocument.spreadsheet';
    case OdsSpreadsheetTemplate = 'application/vnd.oasis.opendocument.spreadsheet-template';
    case OdpPresentation = 'application/vnd.oasis.opendocument.presentation';
    case OdpPresentationTemplate = 'application/vnd.oasis.opendocument.presentation-template';
    case OdgGraphics = 'application/vnd.oasis.opendocument.graphics';
    case OdgGraphicsTemplate = 'application/vnd.oasis.opendocument.graphics-template';
    case OdcChart = 'application/vnd.oasis.opendocument.chart';
    case OdfFormula = 'application/vnd.oasis.opendocument.formula';
    case OdbDatabase = 'application/vnd.oasis.opendocument.database';
    case OdiImage = 'application/vnd.oasis.opendocument.image';
    
    // iWork
    case IworkKeynote = 'application/vnd.apple.keynote';
    case IworkNumbers = 'application/vnd.apple.numbers';
    case IworkPages = 'application/vnd.apple.pages';
    
    // E-books
    case Epub = 'application/epub+zip';
    case Mobi = 'application/x-mobipocket-ebook';
    case AmazonEbook = 'application/vnd.amazon.ebook';
    case Fictionbook = 'application/x-fictionbook+xml';
    
    // Data Exchange
    case AtomXml = 'application/atom+xml';
    case RssXml = 'application/rss+xml';
    case SoapXml = 'application/soap+xml';
    case WsdlXml = 'application/wsdl+xml';
    case XsltXml = 'application/xslt+xml';
    case MathMl = 'application/mathml+xml';
    case Yaml = 'application/x-yaml';
    case Toml = 'application/toml';
    case EdiX12 = 'application/EDI-X12';
    case Edifact = 'application/EDIFACT';
    case Cbor = 'application/cbor';
    case CborSeq = 'application/cbor-seq';
    case Msgpack = 'application/msgpack';
    case Protobuf = 'application/protobuf';
    case Thrift = 'application/vnd.apache.thrift.binary';
    case Avro = 'application/avro';
    case Parquet = 'application/vnd.apache.parquet';
    
    // Binary / Executable
    case JavaArchive = 'application/java-archive';
    case JavaSerializedObject = 'application/java-serialized-object';
    case JavaVm = 'application/java-vm';
    case Wasm = 'application/wasm';
    case XSharedlib = 'application/x-sharedlib';
    case XExecutable = 'application/x-executable';
    case XDosexec = 'application/x-dosexec';
    case XMsdos = 'application/x-msdos-program';
    case XMsdownload = 'application/x-msdownload';
    case XPeExecutable = 'application/vnd.microsoft.portable-executable';
    case XMachoBinary = 'application/x-mach-binary';
    case XElf = 'application/x-elf';
    
    // Fonts
    case FontOtf = 'application/font-otf';
    case FontTtf = 'application/font-ttf';
    case FontWoff = 'application/font-woff';
    case FontWoff2 = 'application/font-woff2';
    case FontSfnt = 'application/font-sfnt';
    case FontCollection = 'application/font-tdpfr';
    
    // Multimedia containers
    case Ogg = 'application/ogg';
    case Mp4 = 'application/mp4';
    case Dash = 'application/dash+xml';
    case Smil = 'application/smil+xml';
    case Mxf = 'application/mxf';
    
    // Geo
    case Geo = 'application/geo+json';
    case Gpx = 'application/gpx+xml';
    case Kml = 'application/vnd.google-earth.kml+xml';
    case Kmz = 'application/vnd.google-earth.kmz';
    case Gml = 'application/gml+xml';
    
    // 3D / CAD
    case Gltf = 'application/gltf+json';
    case GltfBinary = 'application/gltf-binary';
    case Step = 'application/step';
    case Iges = 'application/iges';
    case VndAutocadDwg = 'application/vnd.autocad.dwg';
    case Stl = 'application/sla';
    case X3d = 'application/vnd.hzn-3d-crossword';
    case Obj = 'application/x-tgif';
    
    // Security / Crypto
    case Pkcs7Mime = 'application/pkcs7-mime';
    case Pkcs7Signature = 'application/pkcs7-signature';
    case Pkcs8 = 'application/pkcs8';
    case Pkcs10 = 'application/pkcs10';
    case Pkcs12 = 'application/x-pkcs12';
    case Pkix = 'application/pkix-cert';
    case PkixCrl = 'application/pkix-crl';
    case Pkixcmp = 'application/pkixcmp';
    case XPemFile = 'application/x-pem-file';
    case XPkcs7Certificates = 'application/x-pkcs7-certificates';
    case XPkcs7Certreqresp = 'application/x-pkcs7-certreqresp';
    case XPkcs12 = 'application/x-pkcs12';
    case XSshKey = 'application/x-ssh-key';
    case Pgp = 'application/pgp-encrypted';
    case PgpKeys = 'application/pgp-keys';
    case PgpSignature = 'application/pgp-signature';
    case Jwk = 'application/jwk+json';
    case JwkSet = 'application/jwk-set+json';
    case Jws = 'application/jws';
    case Jwe = 'application/jwe';
    case Cose = 'application/cose';
    case CoseKey = 'application/cose-key';
    case CoseKeySet = 'application/cose-key-set';
    case Cwt = 'application/cwt';
    
    // API / Web
    case FormUrlencoded = 'application/x-www-form-urlencoded';
    case GraphQL = 'application/graphql+json';
    case GraphQLResponse = 'application/graphql-response+json';
    case Hal = 'application/hal+json';
    case HalXml = 'application/hal+xml';
    case Jsonapi = 'application/vnd.api+json';
    case Problem = 'application/problem+json';
    case ProblemXml = 'application/problem+xml';
    case Merge = 'application/merge-patch+json';
    case Activity = 'application/activity+json';
    case Scim = 'application/scim+json';
    
    // Cycle
    case Sql = 'application/sql';
    case Sqlite3 = 'application/vnd.sqlite3';
    case XSqlite3 = 'application/x-sqlite3';
    
    // Archives / Packages
    case XArj = 'application/x-arj';
    case XAce = 'application/x-ace-compressed';
    case XLzh = 'application/x-lzh-compressed';
    case XLzip = 'application/x-lzip';
    case XLzma = 'application/x-lzma';
    case XLzop = 'application/x-lzop';
    case XSnappy = 'application/x-snappy-framed';
    case XCompress = 'application/x-compress';
    case XCpio = 'application/x-cpio';
    case XShar = 'application/x-shar';
    case XStuffit = 'application/x-stuffit';
    case XStuffitx = 'application/x-stuffitx';
    case XGtar = 'application/x-gtar';
    case XUstar = 'application/x-ustar';
    case XCab = 'application/vnd.ms-cab-compressed';
    case Xz = 'application/x-xz';
    case Lz4 = 'application/x-lz4';
    case Br = 'application/x-br';
    
    // Package managers
    case Deb = 'application/vnd.debian.binary-package';
    case Rpm = 'application/x-rpm';
    case XRedhatPackageManager = 'application/x-redhat-package-manager';
    case Apk = 'application/vnd.android.package-archive';
    case Dmg = 'application/x-apple-diskimage';
    case Msi = 'application/x-msi';
    case Pkg = 'application/x-xar';
    case Snap = 'application/vnd.snap';
    case Flatpak = 'application/vnd.flatpak';
    case AppImage = 'application/vnd.appimage';
    case Npm = 'application/vnd.npm.manifest+json';
    case Composer = 'application/vnd.composer.package+json';
    
    // Adobe
    case AdobeAir = 'application/vnd.adobe.air-application-installer-package+zip';
    case Flash = 'application/x-shockwave-flash';
    case Fxp = 'application/vnd.adobe.fxp';
    case XIndesign = 'application/x-indesign';
    case Illustrator = 'application/vnd.adobe.illustrator';
    
    // Other
    case XBittorrent = 'application/x-bittorrent';
    case XNzb = 'application/x-nzb';
    case Xspf = 'application/xspf+xml';
    case VndMsPkiStl = 'application/vnd.ms-pki.stl';
    case VndMsPkiSeccat = 'application/vnd.ms-pki.seccat';
    case VndAndroidObb = 'application/vnd.android.obb';
    case VndSymbianInstall = 'application/vnd.symbian.install';
    case VndTcpdump = 'application/vnd.tcpdump.pcap';
    case Xpcap = 'application/x-pcap';
    
    // OpenAPI / Schema
    case OpenApi = 'application/vnd.oai.openapi+json';
    case OpenApiYaml = 'application/vnd.oai.openapi';
    case JsonSchema = 'application/schema+json';
    case JsonSchemaInstance = 'application/schema-instance+json';
    
    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if given MIME type is an application type
     */
    public static function isApplication(string $mimeType): bool
    {
        return self::tryFrom($mimeType) !== null 
            || str_starts_with($mimeType, 'application/');
    }

    /**
     * @return list<self>
     */
    public static function archives(): array
    {
        return [
            self::Zip,
            self::Gzip,
            self::Bzip2,
            self::Tar,
            self::Rar,
            self::X7zCompressed,
            self::Xz,
        ];
    }

    /**
     * @return list<self>
     */
    public static function documents(): array
    {
        return [
            self::Pdf,
            self::Msword,
            self::MswordX,
            self::MsExcel,
            self::MsExcelX,
            self::MsPowerpoint,
            self::MsPowerpointX,
            self::OdtText,
            self::OdsSpreadsheet,
            self::OdpPresentation,
        ];
    }

    /**
     * @return list<self>
     */
    public static function data(): array
    {
        return [
            self::Json,
            self::Xml,
            self::Yaml,
            self::Toml,
            self::Cbor,
            self::Msgpack,
        ];
    }
}
