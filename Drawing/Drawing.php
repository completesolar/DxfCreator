<?php namespace DxfCreator\Drawing;

class Drawing implements DrawingInterface
{
    public $pages;
    public $blockDefinitionFiles;

    public function __construct()
    {
        $this->pages = array();
        $this->blockDefinitions = array();
    }

    public function drawRectangle($page, $x1, $y1, $x2, $y2, $options = [])
    {
        $points = array(4);
        $points[0] = [$x1, $y1];
        $points[1] = [$x1, $y2];
        $points[2] = [$x2, $y2];
        $points[3] = [$x2, $y1];
        return $this->pages[$page]->add(new Polygon($points, $options));
    }

    public function drawText($page, $text, $x, $y, $lineHeight, $width = null, $options = [])
    {
        return $this->pages[$page]->add(new MText($text, $x, $y, $lineHeight, $width, $options));
    }

    public function drawPolygon($page, array $points, $options = []){

        return $this->pages[$page]->add(new Polygon($points, $options));
    }

    public function drawLine($page, $x1, $y1, $x2, $y2, $options = [])
    {
        $points = array(2);
        $points[0] = [$x1, $y1];
        $points[1] = [$x2, $y2];

        return $this->pages[$page]->add(new Polygon($points, array_merge(["closed" => false], $options)));
    }

    public function drawCircle($page, $radius, $x, $y, $options = []){

        return $this->pages[$page]->add(new Ellipse($x, $y, $radius, $radius, $options));
    }

    public function addPage($name, $options = [])
    {
        $newPage = new Page($name, $options);
        $this->pages[] = $newPage;
        return max(array_keys($this->pages));
    }

    public function insertPdf($page, $filepath, $pdfPage, $x, $y, $scale, $options = [])
    {
        return $this->pages[$page]->add(new Pdf($filepath, $pdfPage, $x, $y, $scale, $options));
    }

    public function insertImage($page, $filepath, $x, $y, $width, $options = [])
    {
        return $this->pages[$page]->add(new Image($filepath, $x, $y, $width, $options));
    }

    public function insertBlockDefinitionFile($filepath, $names = [])
    {
        $this->blockDefinitionFiles[] = new BlockDefinitionFile($filepath, $names);
    }

    public function insertBlock($page, $name, $x, $y, $scale, $options = [])
    {
        return $this->pages[$page]->add(new Block($name, $x, $y, $scale, $options));
    }



}
