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
    private $header;
    private $tables;
    private $blocks;
    private $entities;
    private $objects;

    public $pages;

    public function __construct()
    {
        $this->header = new Section("HEADER", array());
        $this->tables = new Section("TABLES", array());
        $this->blocks = new Section("BLOCKS", array());
        $this->entities = new Section("ENTITIES", array());
        $this->objects = new Section("OBJECTS", array());
        $this->setHeaderDefaults();

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


    public function addPage($options = null)
    {
        $newPage = new Page($options);
        $this->pages[] = $newPage;
        return max(array_keys($this->pages));
    }

    public function save($filePath)
    {
        $file = "";

        $file = "0\nSECTION\n2\nHEADER\n";

        foreach ($this->header->content as $setting => $content){
            $file .= "9\n$setting\n";
            foreach ($content as $code => $value){
                $file .= "$code\n$value\n";
            }
        }

        $file .= "0\nENDSEC\n";
        $file .= "0\nSECTION\n2\nTABLES\n";

        foreach ($this->tables->content as $table){
            // do stuff
        }

        $file .= "0\nENDSEC\n";
        $file .= "0\nSECTION\n2\nBLOCKS\n";

        foreach ($this->blocks->content as $block){
            // do stuff
        }

        $file .= "0\nENDSEC\n";
        $file .= "0\nSECTION\n2\nENTITIES\n";

        foreach ($this->entities->content as $entity){
            // do stuff
        }

        $file .= "0\nENDSEC\n";
        $file .= "0\nSECTION\n2\nOBJECTS\n";

        foreach ($this->objects->content as $object){
            // do stuff
        }

        $file .= "0\nENDSEC\n";
        $file .= "0\nEOF";

        $success = file_put_contents($filePath . ".dxf", $file);

        //var_dump($file);
        //echo "SUCCESS = " . $success . "!";
    }

    public function setHeaderDefaults()
    {
        $acadVer = [1 => "AC1006"];
        $insBase = [10 => "0.0", 20 => "0.0"];
        $extMin = [10 => "0.0", 20 => "0.0"];
        $extMax = [10 => "0.0", 20 => "0.0"];

        $this->header->content = [
                '$ACADVER' => $acadVer,
                '$INSBASE' => $insBase,
                '$EXTMIN' => $extMin,
                '$EXTMAX' => $extMax
                ];
    }


}
