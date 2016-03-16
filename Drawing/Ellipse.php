<?php
namespace DxfCreator\Drawing;

class Ellipse extends Drawable
{
    public $xRadius;
    public $yRadius;

    public function __construct($xCenter, $yCenter, $xRadius, $yRadius, $options)
    {
        $this->type = "ELLIPSE";
        $this->xRadius = $xRadius;
        $this->yRadius = $yRadius;
        $this->center = [$xCenter, $yCenter];
        $this->position = [$xCenter, $yCenter];
        $this->setOptions($options);
    }
}