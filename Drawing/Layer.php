<?php
namespace DxfCreator\Drawing;

class Layer extends Drawable
{
    public $name;

    public function __construct($name, $options = [])
    {
        $this->name = $name;
        $this->setOptions($options);
    }

    public function getDefaults()
    {
        return array(
                "lineColor" => 0,
                "lineWeight" => "0.13",
                "lineType" => "solid",
                "angle" => "",
                "rotationPoint" => "",
                "layer" => "",
        );
    }


}