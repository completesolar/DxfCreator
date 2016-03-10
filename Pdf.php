<?php
namespace DxfCreator;

class Pdf extends File
{
    public $page;
    public $scaleFactor;

    public function __construct($filepath, $page, $xPosition, $yPosition, $scaleFactor, $options = [])
    {
        $this->filepath = $filepath;
        $this->page = $page;
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->scaleFactor = $scaleFactor;
        $this->type = "PDFUNDERLAY";
    }
}