<?php
namespace DxfCreator\Drawing;

class Polygon extends Drawable
{
    public $points;
    public $closed;
    public $fillColor;
    public $fillType;
    public $fillScale;
    public $fillWeight;

    public function __construct(array $newPoints, array $options = [])
    {
        $this->type = "LWPOLYLINE";
        $this->points = $newPoints;
        $this->setPolygonPosition();
        $this->setOptions($options);
    }

    public function setPolygonPosition()
    {
        $minX = null;
        $minY = null;
        $maxX = null;
        $maxY = null;
        foreach ($this->points as $point){
            $minX = is_null($minX) || $point[0] < $minX ? $point[0] : $minX;
            $minY = is_null($minY) || $point[1] < $minY ? $point[1] : $minY;
            $maxX = is_null($maxX) || $point[0] > $maxX ? $point[0] : $maxX;
            $maxY = is_null($maxY) || $point[1] > $maxY ? $point[1] : $maxY;
        }

        $this->position = [$minX, $minY];
        $this->center = [($minX + $maxX)/2, ($minY + $maxY)/2];
    }


    public function setOptions($optionsGiven)
    {
        parent::setOptions($optionsGiven);
        $options = array_replace($this->getPolygonDefaults(), $optionsGiven);

        $this->closed = $options["closed"];
        $this->fillType = $this->setFillType($options["fillType"]);
        $this->fillScale = $options["fillScale"];
        $this->fillColor = $this->setColor($options["fillColor"]);
        $this->fillWeight = $this->setWeight($options["fillWeight"]);
    }

    public function setFillType($givenFillType)
    {
        $fillTypes = ["NONE", "SOLID", "ANSI31"];

        if (is_int($givenFillType) && array_key_exists($givenFillType, $fillTypes)){
            return $fillTypes[$givenFillType];
        }

        if (array_search(strtoupper($givenFillType), $fillTypes) !== false){
            return strtoupper($givenFillType);
        }

        return $fillTypes[0];
    }

    public function getPolygonDefaults()
    {
        return array(
                "fillScale" => 1.0,
                "fillColor" => "black",
                "fillType" => "NONE",
                "fillWeight" => 0.13,
                "closed" => "true",
        );
    }
}