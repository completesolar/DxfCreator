<?php
namespace DxfCreator\Drawing;

class BlockDefinitionFile
{
    public $names;
    public $filepath;

    public function __construct($filepath, $names = [])
    {
        $this->filepath = $filepath;
        $this->names = $names;
    }
}