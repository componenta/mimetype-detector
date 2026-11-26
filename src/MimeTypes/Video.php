<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Video: string
{
    // BEGIN GENERATED MIME CASES
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
    case H264 = 'video/h264';
    case H264Rcdo = 'video/h264-rcdo';
    case H264Svc = 'video/h264-svc';
    case H265 = 'video/h265';
    case Hevc = 'video/hevc';
    case H266 = 'video/h266';
    case Vvc = 'video/vvc';
    case Av1 = 'video/av1';
    case Vp8 = 'video/vp8';
    case Vp9 = 'video/vp9';
    case H261 = 'video/h261';
    case H263 = 'video/h263';
    case H2631998 = 'video/h263-1998';
    case H2632000 = 'video/h263-2000';
    case Jpeg = 'video/jpeg';
    case Jpeg2000 = 'video/jpeg2000';
    case Jpm = 'video/jpm';
    case Jpx = 'video/jpx';
    case Mj2 = 'video/mj2';
    case Mp1s = 'video/mp1s';
    case Mp2p = 'video/mp2p';
    case Mp2t = 'video/mp2t';
    case Mp4vEs = 'video/mp4v-es';
    case Mpv = 'video/mpv';
    case Mpeg4Generic = 'video/mpeg4-generic';
    case Raw = 'video/raw';
    case Theora = 'video/theora';
    public const MsAsf = self::Asf;
    case MsWm = 'video/x-ms-wm';
    case MsWmx = 'video/x-ms-wmx';
    case MsWvx = 'video/x-ms-wvx';
    case VndMsPlayready = 'video/vnd.ms-playready.media.pyv';
    case XM4v = 'video/x-m4v';
    case VndAppleMpegurl = 'video/vnd.apple.mpegurl';
    case VndDvbFile = 'video/vnd.dvb.file';
    case VndDvbH264 = 'video/vnd.dlna.mpeg-tts';
    case Iso = 'video/iso.segment';
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
    case VndCctv = 'video/vnd.cctv';
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
    case Bmpeg = 'video/bmpeg';
    case Bt656 = 'video/bt656';
    case CelB = 'video/celb';
    case Dv = 'video/dv';
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
    case XSgiMovie = 'video/x-sgi-movie';
    case XMng = 'video/x-mng';
    case XF4v = 'video/x-f4v';
    case XOgm = 'video/x-ogm+ogg';
    case XTheora = 'video/x-theora+ogg';
    case XDirac = 'video/x-dirac';
    // END GENERATED MIME CASES

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
        $value = \Componenta\Detector\Internal\MediaTypeParser::tryNormalize($mimeType);
        return $value !== null && str_starts_with($value, 'video/');
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
    public static function commonWebFormats(): array
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
