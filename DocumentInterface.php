<?php namespace DxfCreator;

interface DocumentInterface
{
    /**
     * Add a page to the Shape document.
     * Specifications can be defined in an Options array
     *
     * @param unknown $pageOptions
     * @return Cad
     */
    public function addPage($name, $pageOptions = []);

    public function drawRectangle($page, $x1Position,
            $y1Position, $x2Position, $y2Position, $drawingOptions = []);

    // For now this is just a facade for drawParagraph
    public function drawText($page, $text, $xPosition,
            $yPosition, $lineHeight, $width = null, $textOptions = []);

    public function drawPolygon (array $points, Page $page, $xPosition,
            $yPosition, $drawingOptions = []);

    public function drawLine(Page $page, $x1Position,
            $y1Position, $x2Position, $y2Position, $drawingOptions = []);

    public function drawCircle($radius, Page $page, $xPosition,
            $yPosition, $drawingOptions = []);

    public function insertPdf($page, $filepath, $pdfPage, $xPosition, $yPosition, $scaleFactor, $options = []);

    public function insertImage($page, $filepath, $xPosition, $yPosition, $width, $options = []);



}
