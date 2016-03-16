<?php
namespace DxfCreator\Drawing;

class Pdf extends File
{
    public $page;
    public $scaleFactor;

    public function __construct($filepath, $page, $x, $y, $scale, $options = [])
    {
        $this->filepath = $filepath;
        $this->page = $page;
        $this->position = [$x, $y];
        $this->scaleFactor = $scale;
        $this->type = "PDFUNDERLAY";
    }
}