<?php
namespace DxfCreator\Drawing;

class Text extends Drawable
{
    public $text;
    public $lineHeight;
    public $horizontalAlignment;
    public $verticalAlignment;
    public $angle;

    public function __construct($text, $x, $y, $lineHeight, $textOptions = [])
    {
        $this->type = "TEXT";
        $this->text = $text;
        $this->position = [$x, $y];
        $this->lineHeight = $lineHeight;
        $this->setOptions($textOptions);
    }

    public function setOptions($optionsGiven = [])
    {
        parent::setOptions($optionsGiven);
        $options = array_replace($this->getTextDefaults(), $optionsGiven);

        $this->setAlignment($options["alignment"]);
        $this->angle = $options["angle"];
    }


    public function getTextDefaults()
    {
        return array(
                "alignment" => "top left",
                "sideways" => false,
                "angle" => 0,
        );
    }

    public function setAlignment($alignment)
    {
        $alignments = [
                "top left", "top center", "top right",
                "middle left", "middle center", "middle right",
                "bottom left", "bottom center", "bottom right"
        ];

        if (is_int($alignment)){
            $alignmentIndex = $alignment - 1;
        } else {
            $index = array_search(strtolower($alignment), $alignments);
            $alignmentIndex = $index !== false ? $index : 0;
        }

        switch (true){
            case $alignmentIndex >= 0 && $alignmentIndex < 3:
                $this->verticalAlignment = 3;
                break;
            case $alignmentIndex >= 3 && $alignmentIndex < 6:
                $this->verticalAlignment = 2;
                break;
            case $alignmentIndex >= 6 && $alignmentIndex < 9:
                $this->verticalAlignment = 1;
                break;
            default:
                $this->verticalAlignment = 3;
                break;
        }

        switch (true){
            case $alignmentIndex % 3 == 0:
                $this->horizontalAlignment = 0;
                break;
            case $alignmentIndex % 3 == 1:
                $this->horizontalAlignment = 1;
                break;
            case $alignmentIndex % 3 == 2:
                $this->horizontalAlignment = 2;
                break;
            default:
                $this->horizontalAlignment = 0;
                break;
        }
    }
}