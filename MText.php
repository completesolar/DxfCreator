<?php
namespace DxfCreator;

use DxfCreator\Shape;

class MText extends Shape
{
    public $text;
    public $font;
    public $lineHeight;
    public $bold;
    public $italic;
    public $underline;
    public $width;

    public function __construct($text, $xPosition, $yPosition, $lineHeight, $width = null, $textOptions = null)
    {
        $this->type = "MTEXT";
        $this->text = $text;
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->lineHeight = $lineHeight;
        $this->width = empty($width)? 50.0 : $width;
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