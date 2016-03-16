<?php
namespace DxfCreator\Drawing;

class Block extends Entity
{
    public $name;
    public $scale;

    public function __construct($name, $x, $y, $scale, $options = [])
    {
        $this->name = $name;
        $this->position = [$x, $y];
        $this->scale = $scale;
        $this->type = "INSERT";

        $this->setOptions($options);
    }

    public function setOptions($optionsGiven)
    {
        $options = array_replace($this->getDefaults(), $optionsGiven);

        $this->angle = $options["angle"];
        $this->rotationPoint = $options["rotationPoint"];
    }

    public function getDefaults()
    {
        return array(
                "angle" => 0,
                "rotationPoint" => $this->position,
        );
    }
}