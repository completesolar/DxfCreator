<?php
namespace DxfCreator\Drawing;

class Image extends File
{
    public $widthInch;
    public $widthPx;
    public $heightPx;

    public function __construct($filepath, $x, $y, $width, $options = [])
    {
        $this->filepath = $filepath;
        $this->position = [$x, $y];
        $this->widthInch = $width;
        $this->type = "IMAGE";

        $this->setSizeInPixels();
        $this->setOptions($options);
    }

    public function setSizeInPixels()
    {
        $size = getimagesize($this->filepath);
        $this->widthPx = $size[0];
        $this->heightPx = $size[1];

        $xCenter = $this->widthInch/2;
        $yCenter = ($this->widthInch / $this->widthPx) * $this->heightPx / 2;
        $this->center = [$xCenter, $yCenter];
    }

    public function setOptions($optionsGiven)
    {
        $options = array_replace($this->getDefaults(), $optionsGiven);

        $this->angle = $options["angle"];
        $this->rotationPoint = $this->setRotationPoint($options["rotationPoint"]);
        $this->layer = $options["layer"];
    }

    public function setRotationPoint($rotationPoint)
    {
        if (is_array($rotationPoint) && is_numeric($rotationPoint[0]) && is_numeric($rotationPoint[1])){
            return $rotationPoint;
        }

        return $this->center;
    }

    public function getDefaults()
    {
        return array(
                "angle" => 0,
                "rotationPoint" => $this->center,
                "layer" => 0,
        );
    }
}