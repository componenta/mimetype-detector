<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Audio: string
{
    // Common
    case Aac = 'audio/aac';
    case Ac3 = 'audio/ac3';
    case Aiff = 'audio/aiff';
    case XAiff = 'audio/x-aiff';
    case Amr = 'audio/amr';
    case AmrWb = 'audio/amr-wb';
    case Ape = 'audio/ape';
    case XApe = 'audio/x-ape';
    case Au = 'audio/basic';
    case Dts = 'audio/vnd.dts';
    case DtsHd = 'audio/vnd.dts.hd';
    case Eac3 = 'audio/eac3';
    case Flac = 'audio/flac';
    case XFlac = 'audio/x-flac';
    case M4a = 'audio/mp4';
    case XM4a = 'audio/x-m4a';
    case Midi = 'audio/midi';
    case XMidi = 'audio/x-midi';
    case Mp3 = 'audio/mpeg';
    case Mp3Alt = 'audio/mp3';
    case Ogg = 'audio/ogg';
    case Opus = 'audio/opus';
    case Wav = 'audio/wav';
    case XWav = 'audio/x-wav';
    case Weba = 'audio/webm';
    case Wma = 'audio/x-ms-wma';
    case Xm = 'audio/xm';
    
    // High resolution / Lossless
    case XAls = 'audio/x-als';
    case Dsf = 'audio/dsf';
    case XDsf = 'audio/x-dsf';
    case Dff = 'audio/dff';
    case XDff = 'audio/x-dff';
    case XTta = 'audio/x-tta';
    case XWavpack = 'audio/x-wavpack';
    /** Alias of XApe. */
    public const XMonkeysAudio = self::XApe;
    case XTak = 'audio/x-tak';
    case XOptimfrog = 'audio/x-ofr';
    case XShorten = 'audio/x-shn';
    
    // Tracker / Module
    case XMod = 'audio/x-mod';
    case XS3m = 'audio/x-s3m';
    case XIt = 'audio/x-it';
    case XXm = 'audio/x-xm';
    
    // Speech / Voice
    case Silk = 'audio/silk';
    case Speex = 'audio/speex';
    case Vorbis = 'audio/vorbis';
    case ThreeGpp = 'audio/3gpp';
    case ThreeGpp2 = 'audio/3gpp2';
    case Ilbc = 'audio/ilbc';
    case Evrc = 'audio/evrc';
    case Evrc0 = 'audio/evrc0';
    case Evrc1 = 'audio/evrc1';
    case EvrcB = 'audio/evrcb';
    case EvrcB0 = 'audio/evrcb0';
    case EvrcB1 = 'audio/evrcb1';
    case EvrcNw = 'audio/evrcnw';
    case EvrcNw0 = 'audio/evrcnw0';
    case EvrcNw1 = 'audio/evrcnw1';
    case EvrcWb = 'audio/evrcwb';
    case EvrcWb0 = 'audio/evrcwb0';
    case EvrcWb1 = 'audio/evrcwb1';
    case G711 = 'audio/g711-0';
    case G7221 = 'audio/g7221';
    case G722 = 'audio/g722';
    case G723 = 'audio/g723';
    case G726 = 'audio/g726-16';
    case G728 = 'audio/g728';
    case G729 = 'audio/g729';
    case G7291 = 'audio/g7291';
    case Gsm = 'audio/gsm';
    case GsmEfr = 'audio/gsm-efr';
    case GsmHr08 = 'audio/gsm-hr-08';
    case Pcma = 'audio/pcma';
    case PcmaWb = 'audio/pcma-wb';
    case Pcmu = 'audio/pcmu';
    case PcmuWb = 'audio/pcmu-wb';
    case Qcelp = 'audio/qcelp';
    case Smv = 'audio/smv';
    case Smv0 = 'audio/smv0';
    case SmvQcp = 'audio/smv-qcp';
    
    // Streaming / Special
    case MpegUrl = 'audio/x-mpegurl';
    case Scpls = 'audio/x-scpls';
    case Pls = 'audio/x-pls';
    case VndRn = 'audio/vnd.rn-realaudio';
    case XPnRealaudio = 'audio/x-pn-realaudio';
    case XPnRealaudioPlugin = 'audio/x-pn-realaudio-plugin';
    case XRealaudio = 'audio/x-realaudio';
    
    // Vendor specific
    case VndDeceAudio = 'audio/vnd.dece.audio';
    case VndDigitalWinds = 'audio/vnd.digital-winds';
    case VndDlnaAdts = 'audio/vnd.dlna.adts';
    case VndDra = 'audio/vnd.dra';
    case VndDvbFile = 'audio/vnd.dvb.file';
    case VndEverad = 'audio/vnd.everad.plj';
    case VndHns = 'audio/vnd.hns.audio';
    case VndLucentVoice = 'audio/vnd.lucent.voice';
    case VndMsPlayready = 'audio/vnd.ms-playready.media.pya';
    case VndNokiaMobile = 'audio/vnd.nokia.mobile-xmf';
    case VndNortel = 'audio/vnd.nortel.vbk';
    case VndNuera = 'audio/vnd.nuera.ecelp4800';
    case VndNuera7470 = 'audio/vnd.nuera.ecelp7470';
    case VndNuera9600 = 'audio/vnd.nuera.ecelp9600';
    case VndOctel = 'audio/vnd.octel.sbc';
    case VndQcelp = 'audio/vnd.qcelp';
    case VndRhetorix = 'audio/vnd.rhetorex.32kadpcm';
    case VndRip = 'audio/vnd.rip';
    case VndSealedmedia = 'audio/vnd.sealedmedia.softseal.mpeg';
    case VndVmx = 'audio/vnd.vmx.cvsd';
    
    // Other
    case Adpcm = 'audio/adpcm';
    case Aptx = 'audio/aptx';
    case Atrac3 = 'audio/atrac3';
    case AtracAdvancedLossless = 'audio/atrac-advanced-lossless';
    case AtracX = 'audio/atrac-x';
    case L8 = 'audio/l8';
    case L16 = 'audio/l16';
    case L20 = 'audio/l20';
    case L24 = 'audio/l24';
    case Lpc = 'audio/lpc';
    case Matroska = 'audio/x-matroska';
    case MelodyRcp = 'audio/vnd.cns.anp1';
    case MelodyInf1 = 'audio/vnd.cns.inf1';
    case Mobile = 'audio/mobile-xmf';
    case Mp1 = 'audio/mp1';
    case Mp2 = 'audio/mp2';
    case Mpa = 'audio/mpa';
    case MpaRobust = 'audio/mpa-robust';
    case Musepack = 'audio/x-musepack';
    case Prs = 'audio/prs.sid';
    case Rtp = 'audio/rtp-enc-aescm128';
    case RtpMidi = 'audio/rtp-midi';
    case SofarVorbis = 'audio/x-vorbis+ogg';
    case Sp = 'audio/sp-midi';
    /** Alias of XTta. */
    public const Tta = self::XTta;
    case Ulpfec = 'audio/ulpfec';
    case Usac = 'audio/usac';
    case Vnd3gppIufp = 'audio/vnd.3gpp.iufp';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if given MIME type is an audio type
     */
    public static function isAudio(string $mimeType): bool
    {
        return \Componenta\Detector\MimeType::tryCreate($mimeType)?->isAudio() ?? false;
    }

    /**
     * @return list<self>
     */
    public static function common(): array
    {
        return [
            self::Mp3,
            self::Aac,
            self::Wav,
            self::Ogg,
            self::Flac,
            self::M4a,
            self::Weba,
            self::Opus,
        ];
    }

    /**
     * @return list<self>
     */
    public static function lossless(): array
    {
        return [
            self::Flac,
            self::Wav,
            self::Aiff,
            self::Ape,
            self::Dsf,
            self::Dff,
            self::XTta,
            self::XWavpack,
        ];
    }

    /**
     * @return list<self>
     */
    public static function commonWebFormats(): array
    {
        return [
            self::Mp3,
            self::Aac,
            self::Ogg,
            self::Weba,
            self::Opus,
            self::Wav,
        ];
    }
}
