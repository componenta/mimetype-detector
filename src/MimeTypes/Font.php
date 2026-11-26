<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Font: string
{
    // BEGIN GENERATED MIME CASES
    case Collection = 'font/collection';
    case Otf = 'font/otf';
    case Sfnt = 'font/sfnt';
    case Ttf = 'font/ttf';
    case Woff = 'font/woff';
    case Woff2 = 'font/woff2';
    case ApplicationOtf = 'application/font-otf';
    case ApplicationTtf = 'application/font-ttf';
    case ApplicationWoff = 'application/font-woff';
    case ApplicationWoff2 = 'application/font-woff2';
    case ApplicationSfnt = 'application/font-sfnt';
    case XOtf = 'application/x-font-otf';
    case XTtf = 'application/x-font-ttf';
    case XWoff = 'application/x-font-woff';
    case Eot = 'application/vnd.ms-fontobject';
    case Bdf = 'application/x-font-bdf';
    case Ghostscript = 'application/x-font-ghostscript';
    case LinuxPsf = 'application/x-font-linux-psf';
    case Pcf = 'application/x-font-pcf';
    case Snf = 'application/x-font-snf';
    case Speedo = 'application/x-font-speedo';
    case Type1 = 'application/x-font-type1';
    // END GENERATED MIME CASES

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if given MIME type is a font type
     */
    public static function isFont(string $mimeType): bool
    {
        $value = \Componenta\Detector\Internal\MediaTypeParser::tryNormalize($mimeType);
        return $value !== null && (str_starts_with($value, 'font/') || self::tryFrom($value) !== null);
    }

    /**
     * @return list<self>
     */
    public static function commonWebFormats(): array
    {
        return [
            self::Woff,
            self::Woff2,
            self::Ttf,
            self::Otf,
            self::Eot,
        ];
    }

    /**
     * @return list<self>
     */
    public static function modern(): array
    {
        return [
            self::Woff2,
            self::Woff,
        ];
    }
}
