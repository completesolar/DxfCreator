<?php namespace DxfCreator;

class Section
{
    public $name;
    public $content;
    
    public function __construct($newName = null, $newContent = null)
    {   
        $this->name = $newName;
        $this->content = $newContent;
    }
}
