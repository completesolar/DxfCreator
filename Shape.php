<?php
namespace DXFWriter;

class Shape
{
    public $fillColor;
    public $fillType;
    public $origin;
    public $lineColor;
    public $lineType;
    public $lineWeight;

    public $xPosition;
    public $yPosition;

    public function setOptions($optionsGiven = null)
    {
        $optionsGiven = is_null($optionsGiven) ? [] : $optionsGiven;
        $options = array_replace($this->getDefaults(), $optionsGiven);

        $this->fillColor = $options["fillColor"];
        $this->fillType = $options["fillType"];
        $this->origin = $options["origin"];
        $this->lineColor = $options["lineColor"];
        $this->lineWeight = $options["lineWeight"];
        $this->lineType = $options["lineType"];
    }

    public function getDefaults()
    {
        return array(
                "fillColor" => "none",
                "fillType" => "solid",
                "origin" => "bottom-left",
                "lineColor" => "000000",
                "lineWeight" => 0.1,
                "lineType" => "solid",
        );
    }

}