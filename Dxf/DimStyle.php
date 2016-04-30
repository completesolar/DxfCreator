<?php
namespace DxfCreator\Dxf;

class DimStyle
{
    public $name;
    public $flags;
    public $dimAttributes;

    public function __construct($name)
    {
        $this->name = $name;
        $this->flags = 0;
        $this->dimAttributes = [];

    }
}