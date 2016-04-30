<?php
namespace DxfCreator\Dxf;

class DxfLayer
{
    public $name;
    public $flags;
    public $color;
    public $lineTypeName;
    public $plottingFlag;
    public $lineWeight;


    public function __construct($name)
    {
        $this->name = $name;
        $this->flags = 0;
        $this->color = 7;
        $this->lineTypeName = "Continuous";
        $this->plottingFlag = null;
        $this->lineWeight = -3;
    }
}