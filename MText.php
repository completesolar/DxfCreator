<?php
namespace DxfCreator;

use DxfCreator\Text;

class MText extends Text
{
    public $width;

    public function __construct($text, $xPosition, $yPosition, $width, $lineHeight, $textOptions = null)
    {
        parent::__construct($text, $xPosition, $yPosition, $lineHeight, $textOptions);
        $this->type = "MTEXT";
        $this->width = empty($width)? 50.0 : $width;
    }
}