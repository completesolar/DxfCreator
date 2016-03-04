<?php
namespace DxfCreator;

use DxfCreator\Shape;

class Polygon extends Shape
{
    public $points;

    public function __construct(array $newPoints, array $options = null)
    {
        $this->type = "polygon";
        $this->points = $newPoints;
        $this->setOptions($options);
        $this->setPosition();
    }

    public function setPosition()
    {
        $minX = null;
        $minY = null;
        foreach ($this->points as $point){
            $minX = is_null($minX) || $point[0] < $minX ? $point[0] : $minX;
            $minY = is_null($minY) || $point[1] < $minY ? $point[1] : $minY;
        }

        $this->xPosition = $minX;
        $this->yPosition = $minY;
    }
}