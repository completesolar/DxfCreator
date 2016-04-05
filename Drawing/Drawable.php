<?php
namespace DxfCreator\Drawing;

abstract class Drawable extends Entity
{
    public $fillColor;
    public $fillType;
    public $lineColor;
    public $lineType;
    public $lineWeight;

    public function setOptions($optionsGiven)
    {
        $options = array_replace($this->getDefaults(), $optionsGiven);

        $this->fillColor = $this->setColor($options["fillColor"]);
        $this->fillType = $options["fillType"];
        $this->lineColor = $this->setColor($options["lineColor"]);
        $this->lineWeight = $this->setLineWeight($options["lineWeight"]);
        $this->setLineType($options["lineType"]);
        $this->angle = $options["angle"];
        $this->rotationPoint = $this->setRotationPoint($options["rotationPoint"]);
    }

    public function setRotationPoint($rotationPoint)
    {
        if (is_array($rotationPoint) && is_numeric($rotationPoint[0]) && is_numeric($rotationPoint[1])){
            return $rotationPoint;
        }

        return $this->center;
    }

    public function setColor($color)
    {
        // Note that AutoCAD 'white' prints black if on a white background
        $colors = ["black", "red", "yellow", "green", "cyan", "blue", "violet", "white", "gray", "light_gray"
        ];

        $index = array_search(strtolower($color), $colors);
        if ($index !== false){
            return $index;
        }

        if (is_int($color) && $color >= 0 && $color < count($colors)){
            return $color;
        }

        return 0;
    }

    public function setLineWeight($givenWeight)
    {
        $lineWeights = [0, 0.05, 0.09, 0.13, 0.15, 0.18, 0.2, 0.25, 0.3, 0.35, 0.4, 0.5,
                0.53, 0.6, 0.7, 0.8, 0.9, 1.0, 1.06, 1.2, 1.4, 1.58, 2.0, 2.11,
        ];

        $previousWeight = 0;

        if (!is_numeric($givenWeight)){
            return 0;
        }

        foreach($lineWeights as $index => $weight){
            if ($index > 0){
                if($weight > $givenWeight){
                    if ($weight == $lineWeights[1] && $givenWeight != 0.0){
                        return $weight;
                    }
                    return ($weight - $givenWeight) <= ($givenWeight - $previousWeight) ? $weight : $previousWeight;
                }
            }

            $previousWeight = $weight;
        }

        return $lineWeights[count($lineWeights) - 1];
    }

    public function setLineType($givenType)
    {
        $lineTypes = ["solid", "_", "_ ", ".", "_.", "__."];

        if (is_int($givenType) && array_key_exists($givenType, $lineTypes)){
            $type = $lineTypes[$givenType];
        }

        switch (str_replace('-', '_', strtolower($givenType))){
            case "solid":
                $this->lineType = "Continuous";
                break;
            case "_":
                $this->lineType = "ACAD_ISO02W100";
                break;
            case "_ ":
                $this->lineType = "ACAD_ISO03W100";
                break;
            case ".":
                $this->lineType = "ACAD_ISO07W100";
                break;
            case "_.":
                $this->lineType = "ACAD_ISO10W100";
                break;
            case "__.":
                $this->lineType = "ACAD_ISO11W100";
                break;
            default:
                $this->lineType = "Continuous";
                break;
        }
    }

    public function getDefaults()
    {
        return array(
                "fillColor" => "green",
                "fillType" => "solid",
                "lineColor" => "0",
                "lineWeight" => 0.13,
                "lineType" => "solid",
                "angle" => 0,
                "rotationPoint" => $this->center,
        );
    }
}