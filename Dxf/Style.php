<?php
namespace DxfCreator\Dxf;

class Style
{
    public $name;
    public $flags;
    public $height;
    public $widthFactor;
    public $obliqueAngle;
    public $orientationFlag;
    public $lastHeightUsed;
    public $fontFile;
    public $bigFontFile;
    public $extraFormatInfo;

    public function __construct($name, $fontFile = "arial.ttf")
    {
        $this->name = $name;
        $this->fontFile = $fontFile;
        $this->flags = 0;
        $this->height = 0.2;
        $this->widthFactor = 1.0;
        $this->obliqueAngle = 0.0;
        $this->orientationFlag = 0;
        $this->lastHeightUsed = 0.2;
        $this->bigFontFile = "";
        $this->extraFormatInfo = null;
    }
}