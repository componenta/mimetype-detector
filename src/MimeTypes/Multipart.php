<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Multipart: string
{
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
        return self::tryFrom($mimeType) !== null 
            || str_starts_with($mimeType, 'multipart/');
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
