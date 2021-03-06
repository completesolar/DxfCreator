<?php
namespace DxfCreator\Drawing;

class CustomBlockDefinition
{
    public $name;
    public $shapes;
    public $basePoint;

    public function __construct($name, array $shapes, $x, $y)
    {
        $this->name = $name;
        $this->shapes = $shapes;
        $this->basePoint = [$x, $y];
    }
}