<?php namespace DxfCreator\Drawing;

class Drawing implements DrawingInterface
{
    public $modelSpace;
    public $pages;
    public $blockDefinitionFiles;
    public $customBlockDefinitions;
    public $layers;

    public function __construct()
    {
        $this->modelSpace = new Page("Model");
        $this->pages = array();
        $this->blockDefinitionFiles = array();
        $this->customBlockDefinitions = array();
        $this->layers = array();
    }

    public function drawRectangle($page, $x1, $y1, $x2, $y2, $options = [])
    {
        $points = array(4);
        $points[0] = [$x1, $y1];
        $points[1] = [$x1, $y2];
        $points[2] = [$x2, $y2];
        $points[3] = [$x2, $y1];

        $entity = new Polygon($points, $options);
        return strtolower($page) === "model" ? $this->modelSpace->add($entity) : $this->pages[$page]->add($entity);
    }

    public function drawText($page, $text, $x, $y, $lineHeight, $options = [])
    {
        $entity = new Text($text, $x, $y, $lineHeight, $options);
        return strtolower($page) === "model" ? $this->modelSpace->add($entity) : $this->pages[$page]->add($entity);
    }

    public function drawMText($page, $text, $x, $y, $lineHeight, $width = null, $options = [])
    {
        $entity = new MText($text, $x, $y, $lineHeight, $width, $options);
        return strtolower($page) === "model" ? $this->modelSpace->add($entity) : $this->pages[$page]->add($entity);
    }

    public function drawPolygon($page, array $points, $options = [])
    {
        $entity = new Polygon($points, $options);
        return strtolower($page) === "model" ? $this->modelSpace->add($entity) : $this->pages[$page]->add($entity);
    }

    public function drawLine($page, $x1, $y1, $x2, $y2, $options = [])
    {

        $points = array(2);
        $points[0] = [$x1, $y1];
        $points[1] = [$x2, $y2];

        $entity = new Polygon($points, array_merge(["closed" => false], $options));
        return strtolower($page) === "model" ? $this->modelSpace->add($entity) : $this->pages[$page]->add($entity);
    }

    public function drawCircle($page, $radius, $x, $y, $options = [])
    {
        $entity = new Ellipse($x, $y, $radius, $radius, $options);
        return strtolower($page) === "model" ? $this->modelSpace->add($entity) : $this->pages[$page]->add($entity);
    }

    public function addPage($name, $options = [])
    {
        foreach ($this->pages as $page){
            if (strtolower($page->name) === strtolower($name)){
                throw new \Exception("Page \"$name\" already exists.");
            }
        }

        $newPage = new Page($name, $options);
        $this->pages[] = $newPage;
        return max(array_keys($this->pages));
    }

    public function insertPdf($page, $filepath, $pdfPage, $x, $y, $scale, $options = [])
    {
        $entity = new Pdf($filepath, $pdfPage, $x, $y, $scale, $options);
        return strtolower($page) === "model" ? $this->modelSpace->add($entity) : $this->pages[$page]->add($entity);
    }

    public function insertImage($page, $filepath, $x, $y, $width, $options = [])
    {
        $entity = new Image($filepath, $x, $y, $width, $options);
        return strtolower($page) === "model" ? $this->modelSpace->add($entity) : $this->pages[$page]->add($entity);
    }

    public function insertBlockDefinitionFile($filepath, $names = [])
    {
        $this->blockDefinitionFiles[] = new BlockDefinitionFile($filepath, $names);
    }

    public function insertBlock($page, $name, $x, $y, $scale, $options = [])
    {
        $entity = new Block($name, $x, $y, $scale, $options);
        return strtolower($page) === "model" ? $this->modelSpace->add($entity) : $this->pages[$page]->add($entity);
    }

    public function defineBlock($name, array $shapeRefs, $fromPage, $x, $y)
    {
        $shapes = strtolower($fromPage) === "model" ? $this->modelSpace->detach($shapeRefs) : $this->pages[$fromPage]->detach($shapeRefs);
        $this->customBlockDefinitions[] = new CustomBlockDefinition($name, $shapes, $x, $y);
    }

    public function defineLayer($name, $options = [])
    {
        $this->layers[] = new Layer($name, $options);
    }

    public function drawViewport($page, $x1, $y1, $x2, $y2, $viewCenterX, $viewCenterY, $viewHeight, $frozenLayers = [], $layer = 0)
    {
        $entity = new Viewport($x1, $y1, $x2, $y2, $viewCenterX, $viewCenterY, $viewHeight, $frozenLayers, $layer);
        return strtolower($page) === "model" ? $this->modelSpace->add($entity) : $this->pages[$page]->add($entity);
    }



}
