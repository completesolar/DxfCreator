<?php
namespace DxfCreator;

class Image extends File
{
    public $widthInch;
    public $widthPx;
    public $heightPx;

    public function __construct($filepath, $xPosition, $yPosition, $width, $options = [])
    {
        $this->filepath = $filepath;
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->widthInch = $width;
        $this->type = "IMAGE";

        $this->setSizeInPixels();
        $this->setOptions($options);
    }

    public function setSizeInPixels()
    {
        $size = getimagesize($this->filepath);
        $this->widthPx = $size[0];
        $this->heightPx = $size[1];
    }
}