<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Message: string
{
    case Cpim = 'message/CPIM';
    case DeliveryStatus = 'message/delivery-status';
    case DispositionNotification = 'message/disposition-notification';
    case Example = 'message/example';
    case External = 'message/external-body';
    case FeedbackReport = 'message/feedback-report';
    case Global = 'message/global';
    case GlobalDeliveryStatus = 'message/global-delivery-status';
    case GlobalDispositionNotification = 'message/global-disposition-notification';
    case GlobalHeaders = 'message/global-headers';
    case Http = 'message/http';
    case ImdnXml = 'message/imdn+xml';
    case News = 'message/news';
    case Partial = 'message/partial';
    case Rfc822 = 'message/rfc822';
    case Shttp = 'message/s-http';
    case Sip = 'message/sip';
    case Sipfrag = 'message/sipfrag';
    case TrackingStatus = 'message/tracking-status';
    case VndSiSimp = 'message/vnd.si.simp';
    case VndWfaWsc = 'message/vnd.wfa.wsc';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if given MIME type is a message type
     */
    public static function isMessage(string $mimeType): bool
    {
        return self::tryFrom($mimeType) !== null 
            || str_starts_with($mimeType, 'message/');
    }

    /**
     * @return list<self>
     */
    public static function email(): array
    {
        return [
            self::Rfc822,
            self::Partial,
            self::DeliveryStatus,
            self::DispositionNotification,
        ];
    }
}
