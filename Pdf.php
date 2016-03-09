<?php
namespace DxfCreator;

class Pdf
{
    public $filepath;
    public $page;
    public $xPosition;
    public $yPosition;
    public $scaleFactor;

    public function __construct($filepath, $page, $xPosition, $yPosition, $scaleFactor)
    {
        $this->filepath = $filepath;
        $this->page = $page;
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->scaleFactor = $scaleFactor;
    }
}