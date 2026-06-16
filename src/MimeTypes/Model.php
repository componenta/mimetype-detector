<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Model: string
{
    // Common 3D formats
    case Gltf = 'model/gltf+json';
    case GltfBinary = 'model/gltf-binary';
    case Obj = 'model/obj';
    case Mtl = 'model/mtl';
    case Stl = 'model/stl';
    case XStlAscii = 'model/x.stl-ascii';
    case XStlBinary = 'model/x.stl-binary';
    case Step = 'model/step';
    case StepXml = 'model/step+xml';
    case StepZip = 'model/step+zip';
    case StepXmlZip = 'model/step-xml+zip';
    case Iges = 'model/iges';
    
    // VRML / X3D
    case Vrml = 'model/vrml';
    case X3dVrml = 'model/x3d+vrml';
    case X3dXml = 'model/x3d+xml';
    case X3dBinary = 'model/x3d+binary';
    case X3dFastinfoset = 'model/x3d+fastinfoset';
    case X3dJson = 'model/x3d+json';
    
    // USD (Universal Scene Description)
    case Usd = 'model/vnd.usd';
    case Usda = 'model/vnd.usda';
    case Usdc = 'model/vnd.usdc';
    case Usdz = 'model/vnd.usdz+zip';
    
    // CAD
    case Dwf = 'model/vnd.dwf';
    case Gdl = 'model/vnd.gdl';
    case Gs = 'model/vnd.gs-gdl';
    case Gtw = 'model/vnd.gtw';
    case Mts = 'model/vnd.mts';
    case Parasolid = 'model/vnd.parasolid.transmit.binary';
    case ParasolidText = 'model/vnd.parasolid.transmit.text';
    case PythaPyox = 'model/vnd.pytha.pyox';
    case RosetteRosette = 'model/vnd.rosette.annotated-data-model';
    case Sap = 'model/vnd.sap.vds';
    case Valve = 'model/vnd.valve.source.compiled-map';
    case Vtu = 'model/vnd.vtu';
    
    // Mesh / Geometry
    case Mesh = 'model/mesh';
    case Prc = 'model/prc';
    case U3d = 'model/u3d';
    case Jt = 'model/JT';
    
    // 3MF
    case ThreeMf = 'model/3mf';
    
    // Other
    case E57 = 'model/e57';
    case Example = 'model/example';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if given MIME type is a model type
     */
    public static function isModel(string $mimeType): bool
    {
        return self::tryFrom($mimeType) !== null 
            || str_starts_with($mimeType, 'model/');
    }

    /**
     * @return list<self>
     */
    public static function common(): array
    {
        return [
            self::Gltf,
            self::GltfBinary,
            self::Obj,
            self::Stl,
            self::Step,
            self::Usdz,
        ];
    }

    /**
     * @return list<self>
     */
    public static function webSafe(): array
    {
        return [
            self::Gltf,
            self::GltfBinary,
            self::Usdz,
        ];
    }

    /**
     * @return list<self>
     */
    public static function printing3d(): array
    {
        return [
            self::Stl,
            self::ThreeMf,
            self::Obj,
            self::Step,
        ];
    }
}
