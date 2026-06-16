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
    case XMonkeysAudio = 'audio/x-ape';
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
    case Ilbc = 'audio/iLBC';
    case Evrc = 'audio/EVRC';
    case Evrc0 = 'audio/EVRC0';
    case Evrc1 = 'audio/EVRC1';
    case EvrcB = 'audio/EVRCB';
    case EvrcB0 = 'audio/EVRCB0';
    case EvrcB1 = 'audio/EVRCB1';
    case EvrcNw = 'audio/EVRCNW';
    case EvrcNw0 = 'audio/EVRCNW0';
    case EvrcNw1 = 'audio/EVRCNW1';
    case EvrcWb = 'audio/EVRCWB';
    case EvrcWb0 = 'audio/EVRCWB0';
    case EvrcWb1 = 'audio/EVRCWB1';
    case G711 = 'audio/G711-0';
    case G7221 = 'audio/G7221';
    case G722 = 'audio/G722';
    case G723 = 'audio/G723';
    case G726 = 'audio/G726-16';
    case G728 = 'audio/G728';
    case G729 = 'audio/G729';
    case G7291 = 'audio/G7291';
    case Gsm = 'audio/GSM';
    case GsmEfr = 'audio/GSM-EFR';
    case GsmHr08 = 'audio/GSM-HR-08';
    case Pcma = 'audio/PCMA';
    case PcmaWb = 'audio/PCMA-WB';
    case Pcmu = 'audio/PCMU';
    case PcmuWb = 'audio/PCMU-WB';
    case Qcelp = 'audio/QCELP';
    case Smv = 'audio/SMV';
    case Smv0 = 'audio/SMV0';
    case SmvQcp = 'audio/SMV-QCP';
    
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
    case L8 = 'audio/L8';
    case L16 = 'audio/L16';
    case L20 = 'audio/L20';
    case L24 = 'audio/L24';
    case Lpc = 'audio/LPC';
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
    case Tta = 'audio/x-tta';
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
        return self::tryFrom($mimeType) !== null 
            || str_starts_with($mimeType, 'audio/');
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
    public static function webSafe(): array
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
