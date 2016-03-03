<?php namespace DXFWriter;

use DXFWriter\DocumentInterface;
use DXFWriter\Section;
use DXFWriter\Page;
use DXFWriter\Polygon;
use DXFWriter\Ellipse;
use DXFWriter\Line;
use DXFWriter\Text;

class CadMaker implements DocumentInterface
{

    public $pages;

    public function __construct()
    {
        $this->pages = array();
    }

    public function drawRectangle($page, $x1Position,
            $y1Position, $x2Position, $y2Position, $drawingOptions = null){

        $points = array(4);
        $points[0] = [$x1Position, $y1Position];
        $points[1] = [$x1Position, $y2Position];
        $points[2] = [$x2Position, $y2Position];
        $points[3] = [$x2Position, $y1Position];
        return $this->pages[$page]->add(new Polygon($points, $drawingOptions));
    }

    public function drawText ($text, Page $page, $xPosition,
            $yPosition, $textOptions){

    }

    public function drawPolygon (array $points, Page $page, $xPosition,
            $yPosition, $drawingOptions){

    }

    public function drawLine(Page $page, $x1Position,
            $y1Position, $x2Position, $y2Position, $drawingOptions){

    }

    public function drawCircle($radius, Page $page, $xPosition,
            $yPosition, $drawingOptions){

    }

    public function addPage($name, $options = null)
    {
        $newPage = new Page($name, $options);
        $this->pages[] = $newPage;
        return max(array_keys($this->pages));
    }

}
