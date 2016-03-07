<?php
namespace DxfCreator;

use DxfCreator\Shape;

class Text extends Shape
{
    public $text;
    public $font;
    public $lineHeight;
    public $bold;
    public $italic;
    public $underline;

    public function __construct($text, $xPosition, $yPosition, $lineHeight, $textOptions = null)
    {
        $this->type = "TEXT";
        $this->text = $text;
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->lineHeight = $lineHeight;
        $this->setOptions($textOptions);
    }

    public function setOptions($optionsGiven = null)
    {
        parent::setOptions($optionsGiven);
        $optionsGiven = is_null($optionsGiven) ? [] : $optionsGiven;
        $options = array_replace($this->getTextDefaults(), $optionsGiven);

        $this->font = $options["font"];
        $this->origin = $this->setOrigin($options["origin"]);
        $this->bold = $options["bold"];
        $this->italic = $options["italic"];
        $this->underline = $options["underline"];
    }


    public function getTextDefaults()
    {
        return array(
                "font" => "Arial",
                "origin" => "top left",
                "bold" => false,
                "italic" => false,
                "underline" => false,
        );
    }

}