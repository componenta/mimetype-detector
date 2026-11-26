<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Multipart: string
{
    // BEGIN GENERATED MIME CASES
    case Alternative = 'multipart/alternative';
    case Appledouble = 'multipart/appledouble';
    case Byteranges = 'multipart/byteranges';
    case Digest = 'multipart/digest';
    case Encrypted = 'multipart/encrypted';
    case Example = 'multipart/example';
    case FormData = 'multipart/form-data';
    case HeaderSet = 'multipart/header-set';
    case Mixed = 'multipart/mixed';
    case Multilingual = 'multipart/multilingual';
    case Parallel = 'multipart/parallel';
    case Related = 'multipart/related';
    case Report = 'multipart/report';
    case Signed = 'multipart/signed';
    case VndBintMedPlus = 'multipart/vnd.bint.med-plus';
    case VoiceMessage = 'multipart/voice-message';
    case XMixedReplace = 'multipart/x-mixed-replace';
    // END GENERATED MIME CASES

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if given MIME type is a multipart type
     */
    public static function isMultipart(string $mimeType): bool
    {
        $value = \Componenta\Detector\Internal\MediaTypeParser::tryNormalize($mimeType);
        return $value !== null && str_starts_with($value, 'multipart/');
    }

    /**
     * @return list<self>
     */
    public static function common(): array
    {
        return [
            self::FormData,
            self::Mixed,
            self::Alternative,
            self::Related,
        ];
    }
}
