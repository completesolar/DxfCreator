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
    public function addPage($name, $pageOptions = null);

    public function drawRectangle($page, $x1Position,
            $y1Position, $x2Position, $y2Position, $drawingOptions = null);

    // For now this is just a facade for drawParagraph
    public function drawText($page, $text, $xPosition,
            $yPosition, $lineHeight, $width = null, $textOptions = null);

    public function drawParagraph($page, $text, $xPosition, $yPosition, $width,
            $lineHeight, $textOptions = null);

    public function drawPolygon (array $points, Page $page, $xPosition,
            $yPosition, $drawingOptions = null);

    public function drawLine(Page $page, $x1Position,
            $y1Position, $x2Position, $y2Position, $drawingOptions = null);

    public function drawCircle($radius, Page $page, $xPosition,
            $yPosition, $drawingOptions = null);



}
