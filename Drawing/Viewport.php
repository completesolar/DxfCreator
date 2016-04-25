<?php
namespace DxfCreator\Drawing;

class Viewport extends Entity
{
    public $paperWidth;
    public $paperHeight;
    public $viewCenterPoint;
    public $viewHeight;
    public $frozenLayers;

    public function __construct($x1, $y1, $x2, $y2, $viewCenterX, $viewCenterY, $viewHeight, $frozenLayers = [], $layer = 0)
    {
        $xStart = $x1 < $x2 ? $x1 : $x2;
        $xEnd = $x1 > $x2 ? $x1 : $x2;
        $yStart = $y1 < $y2 ? $y1 : $y2;
        $yEnd = $y1 > $y2 ? $y1 : $y2;

        $this->type = "VIEWPORT";
        $this->layer = $layer;
        $this->center = [($xStart + $xEnd)/2, ($yStart + $yEnd)/2];
        $this->paperWidth = ($xEnd - $xStart);
        $this->paperHeight = ($yEnd - $yStart);
        $this->viewCenterPoint = [$viewCenterX, $viewCenterY];
        $this->viewHeight = $viewHeight;
        $this->frozenLayers = $frozenLayers;
    }

}