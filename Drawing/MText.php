<?php
namespace DxfCreator\Drawing;

class MText extends Drawable
{
    public $text;
    public $font;
    public $lineHeight;
    public $bold;
    public $italic;
    public $underline;
    public $width;
    public $alignment;

    public function __construct($text, $x, $y, $lineHeight, $width = null, $textOptions = [])
    {
        $this->type = "MTEXT";
        $this->text = $text;
        $this->position = [$x, $y];
        $this->lineHeight = $lineHeight;
        $this->width = empty($width)? 50.0 : $width;
        $this->setOptions($textOptions);
    }

    public function setOptions($optionsGiven = [])
    {
        parent::setOptions($optionsGiven);
        $options = array_replace($this->getTextDefaults(), $optionsGiven);

        $this->font = $options["font"];
        $this->alignment = $this->setAlignment($options["alignment"]);
        $this->bold = $options["bold"];
        $this->italic = $options["italic"];
        $this->underline = $options["underline"];
    }


    public function getTextDefaults()
    {
        return array(
                "font" => "Arial",
                "alignment" => "top left",
                "bold" => false,
                "italic" => false,
                "underline" => false,
        );
    }

    public function setAlignment($alignment)
    {
        $alignments = [
                "top left", "top center", "top right",
                "middle left", "middle center", "middle right",
                "bottom left", "bottom center", "bottom right"
        ];

        $index = array_search(strtolower($alignment), $alignments);
        if ($index !== false){
            return $index + 1;
        }

        if (is_int($alignment) && $alignment >= 1 && $alignment <= count($alignments)){
            return $alignment;
        }

        return 1;
    }

}