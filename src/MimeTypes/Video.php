<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Video: string
{
    // Common
    case Mp4 = 'video/mp4';
    case Mpeg = 'video/mpeg';
    case Ogg = 'video/ogg';
    case Webm = 'video/webm';
    case Quicktime = 'video/quicktime';
    case XFlv = 'video/x-flv';
    case XMsvideo = 'video/x-msvideo';
    case XMatroska = 'video/x-matroska';
    case Avi = 'video/avi';
    case Wmv = 'video/x-ms-wmv';
    case Asf = 'video/x-ms-asf';
    case ThreeGpp = 'video/3gpp';
    case ThreeGpp2 = 'video/3gpp2';
    
    // H.264 / AVC
    case H264 = 'video/H264';
    case H264Rcdo = 'video/H264-RCDO';
    case H264Svc = 'video/H264-SVC';
    
    // H.265 / HEVC
    case H265 = 'video/H265';
    case Hevc = 'video/hevc';
    
    // H.266 / VVC
    case H266 = 'video/H266';
    case Vvc = 'video/vvc';
    
    // AV1
    case Av1 = 'video/AV1';
    
    // VP8/VP9
    case Vp8 = 'video/VP8';
    case Vp9 = 'video/VP9';
    
    // Other codecs
    case H261 = 'video/H261';
    case H263 = 'video/H263';
    case H2631998 = 'video/H263-1998';
    case H2632000 = 'video/H263-2000';
    case Jpeg = 'video/JPEG';
    case Jpeg2000 = 'video/jpeg2000';
    case Jpm = 'video/jpm';
    case Jpx = 'video/jpx';
    case Mj2 = 'video/mj2';
    case Mp1s = 'video/MP1S';
    case Mp2p = 'video/MP2P';
    case Mp2t = 'video/MP2T';
    case Mp4vEs = 'video/MP4V-ES';
    case Mpv = 'video/MPV';
    case Mpeg4Generic = 'video/mpeg4-generic';
    case Raw = 'video/raw';
    case Theora = 'video/theora';
    
    // Microsoft
    case MsAsf = 'video/x-ms-asf';
    case MsWm = 'video/x-ms-wm';
    case MsWmx = 'video/x-ms-wmx';
    case MsWvx = 'video/x-ms-wvx';
    case VndMsPlayready = 'video/vnd.ms-playready.media.pyv';
    
    // Apple
    case XM4v = 'video/x-m4v';
    case VndAppleMpegurl = 'video/vnd.apple.mpegurl';
    
    // DVD / Blu-ray
    case VndDvbFile = 'video/vnd.dvb.file';
    case VndDvbH264 = 'video/vnd.dlna.mpeg-tts';
    case Iso = 'video/iso.segment';
    
    // Streaming
    case VndDeceHd = 'video/vnd.dece.hd';
    case VndDeceMobile = 'video/vnd.dece.mobile';
    case VndDeceMp4 = 'video/vnd.dece.mp4';
    case VndDecePd = 'video/vnd.dece.pd';
    case VndDeceSd = 'video/vnd.dece.sd';
    case VndDeceVideo = 'video/vnd.dece.video';
    case VndDirectvMpeg = 'video/vnd.directv.mpeg';
    case VndDirectvMpegTts = 'video/vnd.directv.mpeg-tts';
    case Rtsp = 'video/vnd.iptvforum.1dparityfec-1010';
    case Rtsp2d = 'video/vnd.iptvforum.1dparityfec-2005';
    case Rtsp2d2 = 'video/vnd.iptvforum.2dparityfec-1010';
    case Rtsp2d22 = 'video/vnd.iptvforum.2dparityfec-2005';
    case Rtspt = 'video/vnd.iptvforum.ttsavc';
    case Rtspt264 = 'video/vnd.iptvforum.ttsmpeg2';
    
    // Vendor specific
    case VndCctv = 'video/vnd.CCTV';
    case VndFvt = 'video/vnd.fvt';
    case VndHns = 'video/vnd.hns.video';
    case VndMotorolaVideo = 'video/vnd.motorola.video';
    case VndMotorolaVideop = 'video/vnd.motorola.videop';
    case VndMpegurl = 'video/vnd.mpegurl';
    case VndNokiaInterleavedMultimedia = 'video/vnd.nokia.interleaved-multimedia';
    case VndNokiaMp4vr = 'video/vnd.nokia.mp4vr';
    case VndNokiaVideovoip = 'video/vnd.nokia.videovoip';
    case VndObjectvideo = 'video/vnd.objectvideo';
    case VndRadgamettoolsBink = 'video/vnd.radgamettools.bink';
    case VndRadgamettoolsSmacker = 'video/vnd.radgamettools.smacker';
    case VndSealedmedia = 'video/vnd.sealed.mpeg1';
    case VndSealedmedia4 = 'video/vnd.sealed.mpeg4';
    case VndSealedmediaSwf = 'video/vnd.sealed.swf';
    case VndSealedmovie = 'video/vnd.sealedmedia.softseal.mov';
    case VndUvvuMp4 = 'video/vnd.uvvu.mp4';
    case VndVivo = 'video/vnd.vivo';
    case VndYoutube = 'video/vnd.youtube.yt';
    
    // Other
    case Bmpeg = 'video/BMPEG';
    case Bt656 = 'video/BT656';
    case CelB = 'video/CelB';
    case Dv = 'video/DV';
    case Encap = 'video/encaprtp';
    case Example = 'video/example';
    case Flexfec = 'video/flexfec';
    case Nv = 'video/nv';
    case Parityfec = 'video/parityfec';
    case PointerRaw = 'video/pointer';
    case RtpEncAescm128 = 'video/rtp-enc-aescm128';
    case Rtploopback = 'video/rtploopback';
    case Rtx = 'video/rtx';
    case Scip = 'video/scip';
    case SmpteRaw = 'video/smpte291';
    case Smpte2022 = 'video/smpte292m';
    case Ulpfec = 'video/ulpfec';
    case VcRdp = 'video/vc1';
    case Vc2 = 'video/vc2';
    
    // Legacy
    case XSgiMovie = 'video/x-sgi-movie';
    case XMng = 'video/x-mng';
    case XF4v = 'video/x-f4v';
    case XOgm = 'video/x-ogm+ogg';
    case XTheora = 'video/x-theora+ogg';
    case XDirac = 'video/x-dirac';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if given MIME type is a video type
     */
    public static function isVideo(string $mimeType): bool
    {
        return self::tryFrom($mimeType) !== null 
            || str_starts_with($mimeType, 'video/');
    }

    /**
     * @return list<self>
     */
    public static function common(): array
    {
        return [
            self::Mp4,
            self::Webm,
            self::Mpeg,
            self::Quicktime,
            self::Avi,
            self::XMatroska,
            self::Wmv,
            self::XFlv,
            self::ThreeGpp,
        ];
    }

    /**
     * @return list<self>
     */
    public static function webSafe(): array
    {
        return [
            self::Mp4,
            self::Webm,
            self::Ogg,
        ];
    }

    /**
     * @return list<self>
     */
    public static function streaming(): array
    {
        return [
            self::VndAppleMpegurl,
            self::Mp2t,
            self::VndMsPlayready,
        ];
    }

    /**
     * @return list<self>
     */
    public static function highQuality(): array
    {
        return [
            self::H265,
            self::Hevc,
            self::Av1,
            self::Vp9,
            self::XMatroska,
        ];
    }
}
