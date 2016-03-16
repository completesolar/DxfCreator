<?php
namespace DxfCreator\Drawing;

class BlockDefinitionFile extends File
{
    public $names;

    public function __construct($filepath, $names = [])
    {
        $this->filepath = $filepath;
        $this->names = $names;
    }
}