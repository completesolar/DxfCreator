<?php
namespace DxfCreator\Dxf;

class LineType
{

    public $name;
    public $flags;
    public $description;
    public $alignmentCode;
    public $lineTypeElementCount;
    public $patternLength;
    public $lengths;

    public function __construct($name)
    {
        $this->name = $name;
        $this->flags = 0;
        $this->description = "";
        $this->alignmentCode = 65;
        $this->lineTypeElementCount = 0;
        $this->patternLength = 0.0;
        $this->lengths = [];
    }
}