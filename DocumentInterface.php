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

    public function drawText ($text, Page $page, $xPosition,
            $yPosition, $textOptions);

    public function drawPolygon (array $points, Page $page, $xPosition,
            $yPosition, $drawingOptions);

    public function drawLine(Page $page, $x1Position,
            $y1Position, $x2Position, $y2Position, $drawingOptions);

    public function drawCircle($radius, Page $page, $xPosition,
            $yPosition, $drawingOptions);



}
