<?php namespace DxfCreator\Drawing;

interface DrawingInterface
{
    /**
     * Add a page to the Shape document.
     * Specifications can be defined in an Options array
     *
     * @param unknown $pageOptions
     * @return Cad
     */
    public function addPage($name, $options = []);

    public function drawRectangle($page, $x1, $y1, $x2, $y2, $drawingOptions = []);

    public function drawText($page, $text, $x, $y, $lineHeight, $options = []);

    public function drawMText($page, $text, $x, $y, $lineHeight, $width = null, $options = []);

    public function drawPolygon ($page, array $points, $options = []);

    public function drawLine($page, $x1, $y1, $x2, $y2, $options = []);

    public function drawCircle($page, $radius, $x, $y, $options = []);

    public function drawViewport($page, $x1, $y1, $x2, $y2, $viewCenterX, $viewCenterY, $viewHeight, $frozenLayers = [], $layer = 0);

    public function insertPdf($page, $filepath, $pdfPage, $x, $y, $scale, $options = []);

    public function insertImage($page, $filepath, $x, $y, $width, $options = []);

    public function insertBlock($page, $name, $x, $y, $scale, $options = []);

    public function insertBlockDefinitionFile($filepath, $names = []);

    public function defineBlock($name, array $shapes, $fromPage, $x, $y);

    public function defineLayer($name, $options);


}
