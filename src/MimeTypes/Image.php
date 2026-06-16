<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Image: string
{
    case Avif = 'image/avif';
    case Bmp = 'image/bmp';
    case Cgm = 'image/cgm';
    case Dicom = 'image/dicom+rle';
    case Emf = 'image/emf';
    case Fits = 'image/fits';
    case G3fax = 'image/g3fax';
    case Gif = 'image/gif';
    case Heic = 'image/heic';
    case HeicSequence = 'image/heic-sequence';
    case Heif = 'image/heif';
    case HeifSequence = 'image/heif-sequence';
    case Hej2k = 'image/hej2k';
    case Hsj2 = 'image/hsj2';
    case Ief = 'image/ief';
    case Jls = 'image/jls';
    case Jp2 = 'image/jp2';
    case Jpeg = 'image/jpeg';
    case Jph = 'image/jph';
    case Jphc = 'image/jphc';
    case Jpm = 'image/jpm';
    case Jpx = 'image/jpx';
    case Jxl = 'image/jxl';
    case Jxr = 'image/jxr';
    case Jxra = 'image/jxra';
    case Jxrs = 'image/jxrs';
    case Jxs = 'image/jxs';
    case Jxsc = 'image/jxsc';
    case Jxsi = 'image/jxsi';
    case Jxss = 'image/jxss';
    case Ktx = 'image/ktx';
    case Ktx2 = 'image/ktx2';
    case Png = 'image/png';
    case Apng = 'image/apng';
    case Pjpeg = 'image/pjpeg';
    case PrsBtif = 'image/prs.btif';
    case PrsPti = 'image/prs.pti';
    case PwgRaster = 'image/pwg-raster';
    case SvgXml = 'image/svg+xml';
    case T38 = 'image/t38';
    case Tiff = 'image/tiff';
    case TiffFx = 'image/tiff-fx';
    case VndAdobePhotoshop = 'image/vnd.adobe.photoshop';
    case VndAirzipAcceleratorAzv = 'image/vnd.airzip.accelerator.azv';
    case VndCnsInf2 = 'image/vnd.cns.inf2';
    case VndDeceGraphic = 'image/vnd.dece.graphic';
    case VndDjvu = 'image/vnd.djvu';
    case VndDwg = 'image/vnd.dwg';
    case VndDxf = 'image/vnd.dxf';
    case VndDvbSubtitle = 'image/vnd.dvb.subtitle';
    case VndFastbidsheet = 'image/vnd.fastbidsheet';
    case VndFpx = 'image/vnd.fpx';
    case VndFst = 'image/vnd.fst';
    case VndFujixeroxEdmicsMmr = 'image/vnd.fujixerox.edmics-mmr';
    case VndFujixeroxEdmicsRlc = 'image/vnd.fujixerox.edmics-rlc';
    case VndGlobeGraphicsBvh = 'image/vnd.globegraphics.bvh';
    case VndMicrosoftIcon = 'image/vnd.microsoft.icon';
    case VndMix = 'image/vnd.mix';
    case VndMozillaApng = 'image/vnd.mozilla.apng';
    case VndMsModi = 'image/vnd.ms-modi';
    case VndMsPhoto = 'image/vnd.ms-photo';
    case VndNetFpx = 'image/vnd.net-fpx';
    case VndPcoB16 = 'image/vnd.pco.b16';
    case VndRadiance = 'image/vnd.radiance';
    case VndSealedPng = 'image/vnd.sealed.png';
    case VndSealedmediaHdImagingJp2 = 'image/vnd.sealedmedia.hd-imaging.jp2';
    case VndSvf = 'image/vnd.svf';
    case VndTencentTap = 'image/vnd.tencent.tap';
    case VndValveSourceTexture = 'image/vnd.valve.source.texture';
    case VndWapWbmp = 'image/vnd.wap.wbmp';
    case VndXiff = 'image/vnd.xiff';
    case VndZbrushPcx = 'image/vnd.zbrush.pcx';
    case Webp = 'image/webp';
    case Wmf = 'image/wmf';
    case XCmuRaster = 'image/x-cmu-raster';
    case XCmx = 'image/x-cmx';
    case XFreehand = 'image/x-freehand';
    case XIcon = 'image/x-icon';
    case XJng = 'image/x-jng';
    case XMrsidImage = 'image/x-mrsid-image';
    case XMsBmp = 'image/x-ms-bmp';
    case XPcx = 'image/x-pcx';
    case XPict = 'image/x-pict';
    case XPortableAnymap = 'image/x-portable-anymap';
    case XPortableBitmap = 'image/x-portable-bitmap';
    case XPortableGraymap = 'image/x-portable-graymap';
    case XPortablePixmap = 'image/x-portable-pixmap';
    case XRgb = 'image/x-rgb';
    case XTga = 'image/x-tga';
    case XXbitmap = 'image/x-xbitmap';
    case XXpixmap = 'image/x-xpixmap';
    case XXwindowdump = 'image/x-xwindowdump';
    case XCur = 'image/x-cur';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if given MIME type is an image type
     */
    public static function isImage(string $mimeType): bool
    {
        return self::tryFrom($mimeType) !== null 
            || str_starts_with($mimeType, 'image/');
    }

    /**
     * Get common/popular image types
     * 
     * @return list<self>
     */
    public static function common(): array
    {
        return [
            self::Jpeg,
            self::Png,
            self::Gif,
            self::Webp,
            self::SvgXml,
            self::Bmp,
            self::Tiff,
            self::VndMicrosoftIcon,
            self::Avif,
            self::Heic,
            self::Heif,
        ];
    }

    /**
     * Get web-safe image types
     * 
     * @return list<self>
     */
    public static function webSafe(): array
    {
        return [
            self::Jpeg,
            self::Png,
            self::Gif,
            self::Webp,
            self::SvgXml,
            self::Avif,
        ];
    }
}
