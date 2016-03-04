<?php namespace DxfCreator;

class Page
{

    public $content;
    public $marginBottom;
    public $marginLeft;
    public $marginRight;
    public $marginTop;
    public $name;
    public $xLength;
    public $yLength;

    public function __construct($newName, $options = null)
    {
        $this->name = $newName;
        $this->setOptions($options);
        $this->content = array();
    }

    public function getDefaults()
    {
        return array(
                "marginBottom" => 0.0,
                "marginLeft" => 0.0,
                "marginRight" => 0.0,
                "marginTop" => 0.0,
                "xLength" => 17.0,
                "yLength" => 11.0,
        );
    }

    public function setOptions($optionsGiven = null)
    {
        $optionsGiven = is_null($optionsGiven) ? [] : $optionsGiven;
        $options = array_replace($this->getDefaults(), $optionsGiven);

        $this->marginBottom = $options["marginBottom"];
        $this->marginLeft = $options["marginLeft"];
        $this->marginRight = $options["marginRight"];
        $this->marginTop = $options["marginTop"];
        $this->xLength = $options["xLength"];
        $this->yLength = $options["yLength"];
    }

    public function add(Shape $shape)
    {
        $this->content[] = $shape;
        return max(array_keys($this->content));
    }
}
