<?php

namespace DxfCreator\Examples;

use DxfCreator\DxfConverter;
use DxfCreator\CadMaker;

class ExampleGenerator
{
    public function exampleTextOrientations($filepath = "Examples/exampleTextOrientations.dxf")
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $cad->drawText($p1, "The squares are references. The Top left corner of each square is the position for each text object.", 1, 10.75, 0.3);
        $cad->drawText($p1, "TL", 1, 10, 0.5, null, ["origin" => "top left"]);
        $cad->drawRectangle($p1, 1, 10, 3, 8);
        $cad->drawText($p1, "TC", 5, 10, 0.5, null, ["origin" => "top center"]);
        $cad->drawRectangle($p1, 5, 10, 7, 8);
        $cad->drawText($p1, "TR", 9, 10, 0.5, null, ["origin" => "top right"]);
        $cad->drawRectangle($p1, 9, 10, 11, 8);
        $cad->drawText($p1, "ML", 1, 7, 0.5, null, ["origin" => "middle left"]);
        $cad->drawRectangle($p1, 1, 7, 3, 5);
        $cad->drawText($p1, "MC", 5, 7, 0.5, null, ["origin" => "middle center"]);
        $cad->drawRectangle($p1, 5, 7, 7, 5);
        $cad->drawText($p1, "MR", 9, 7, 0.5, null, ["origin" => "middle right"]);
        $cad->drawRectangle($p1, 9, 7, 11, 5);
        $cad->drawText($p1, "BL", 1, 4, 0.5, null, ["origin" => "bottom left"]);
        $cad->drawRectangle($p1, 1, 4, 3, 2);
        $cad->drawText($p1, "BC", 5, 4, 0.5, null, ["origin" => "bottom center"]);
        $cad->drawRectangle($p1, 5, 4, 7, 2);
        $cad->drawText($p1, "BR", 9, 4, 0.5, null, ["origin" => "bottom right"]);
        $cad->drawRectangle($p1, 9, 4, 11, 2);
        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }

    public function exampleTextOnMultiplePages($filepath = "Examples/exampleTextOnMultiplePages.dxf")
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $p2 = $cad->addPage("Page 2");
        $p3 = $cad->addPage("Page 3");
        $p4 = $cad->addPage("Page 4");

        $cad->drawText($p1, "This is the text on Page 1. I hope it does its job!", 1, 7, 1, 14);
        $cad->drawText($p2, "This is the text on Page 2. I hope it does its job!", 1, 7, 1, 14);
        $cad->drawText($p3, "This is the text on Page 3. I hope it does its job!", 1, 7, 1, 14);
        $cad->drawText($p4, "This is the text on Page 4. I hope it does its job!", 1, 7, 1, 14);

        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }

    public function exampleTextStyles($filepath ="Examples/exampleTextStyles.dxf")
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $cad->drawText($p1, "Bold", 1, 10, 0.3, null, ["bold" => true]);
        $cad->drawText($p1, "Italic", 2, 9, 0.3, null, ["italic" => true]);
        $cad->drawText($p1, "Underlined", 3, 8, 0.3, null, ["underline" => true]);
        $cad->drawText($p1, "Bold, Italic, and Underline", 4, 7, 0.3, null, ["bold" => true, "italic" => true, "underline" => true]);
        $cad->drawText($p1, "The font is Times New Roman", 5, 6, 0.3, null, ["font" => "Times New Roman"]);
        $cad->drawText($p1, '\LThis\l \Ouses\o \C1;the special\C0; \fArial|b0|i1|c0|p34;format\fArial|b0|i0|c0|p34;'
                . ' \fArial|b1|i0|c0|p34;codes\fArial|b0|i0|c0|p34; \H0.7x;\S^ embedded;\H1.4286x; \H0.5x;\Sin/th'
                . 'e; \H2x;\W2;string \fCourier New|b0|i0|c0|p49;\W1;itself', 6, 5, 0.3);
        $cad->drawText($p1, 'See \C5;\Lhttp://docs.autodesk.com/ACD/2010/ENU/AutoCAD%202010%20User%20Documentation/index.h'
                . 'tml?url=WS1a9193826455f5ffa23ce210c4a30acaf-63b9.htm,topicNumber=d0e123454\l \C0;for details on format '
                . 'codes', 0.5, 4, 0.15);

        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }

    public function exampleDrawTextWithWidthLimit($filepath = "Examples/exampleDrawTextWithWidthLimit.dxf")
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $cad->drawText($p1, "This is text that has a limit on the width of the paragraph.", 2, 5, 0.3, 3);

        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }

    public function exampleDrawText($filepath = "Examples/exampleDrawText.dxf")
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $cad->drawText($p1, "This is text!", 2, 3, 0.2);

        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }

    /*
     * Note that AutoCAD 'white' (index #7) prints black if on a white background
     */
    public function exampleRectangleOptions($filepath = "Examples/exampleRectangleOptions.dxf")
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");

        $cad->drawRectangle($p1, 1, 1, 3, 3, ["lineWeight" => 0.1]);
        $cad->drawRectangle($p1, 2, 2, 4, 4, ["lineColor" => "red", "lineWeight" => 0.25]);
        $cad->drawRectangle($p1, 3, 3, 5, 5, ["lineColor" => "yellow", "lineWeight" => 0.5]);
        $cad->drawRectangle($p1, 4, 4, 6, 6, ["lineColor" => "green", "lineWeight" => 0.75]);
        $cad->drawRectangle($p1, 5, 5, 7, 7, ["lineColor" => 4, "lineWeight" => 1.0]);
        $cad->drawRectangle($p1, 6, 6, 8, 8, ["lineColor" => 5, "lineWeight" => 1.5]);
        $cad->drawRectangle($p1, 7, 7, 9, 9, ["lineColor" => 6, "lineWeight" => 2.0]);
        $cad->drawRectangle($p1, 8, 8, 10, 10, ["lineColor" => 7, "lineWeight" => 2.5]);

        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }

    public function exampleDrawManyRectanglesOnManyPages($filepath = "Examples/exampleDrawManyRectanglesOnManyPages.dxf")
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $p2 = $cad->addPage("Page 2");
        $p3 = $cad->addPage("Page 3");
        $p4 = $cad->addPage("Page 4");
        $p5 = $cad->addPage("Page 5");
        $p6 = $cad->addPage("Page 6");

        $cad->drawRectangle($p1, 0, 0, 3, 3);
        $cad->drawRectangle($p1, 2, 2.5, 7, 9);
        $cad->drawRectangle($p1, 4, 1, 6, 8);

        $cad->drawRectangle($p2, 1, 0, 10, 3);
        $cad->drawRectangle($p2, 2, 4.5, 6, 9);
        $cad->drawRectangle($p2, 4, 1, 6, 1.5);

        $cad->drawRectangle($p3, 1.333, 0.9, 4, 3);
        $cad->drawRectangle($p3, 2.333, 4.5, 6, 7);
        $cad->drawRectangle($p3, 4.333, 1, 10, 3);

        $cad->drawRectangle($p4, 0.5, 0.9, 4, 3);
        $cad->drawRectangle($p4, 10, 4.5, 6, 7);
        $cad->drawRectangle($p4, 4.333, 10, 10, 10.7);

        $cad->drawRectangle($p5, 0.5, 6, 4, 9);
        $cad->drawRectangle($p5, 2, 2, 6, 3);
        $cad->drawRectangle($p5, 4.333, 1, 6, 10.7);

        $cad->drawRectangle($p6, 0.5, 0.9, 7, 3);
        $cad->drawRectangle($p6, 4, 4.5, 6, 7);
        $cad->drawRectangle($p6, 1, 14, 3, 10.7);

        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }

    public function exampleDrawRectangle($filepath = "Examples/exampleDrawRectangle.dxf")
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $cad->drawRectangle($p1, 0, 0, 3, 3);

        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }

    public function exampleFourPages($filepath = "Examples/exampleFourPages.dxf")
    {
        $cad = new CadMaker();
        $cad->addPage("Page 1");
        $cad->addPage("Page 2");
        $cad->addPage("Page 3");
        $cad->addPage("Page 4");

        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }

    public function examplePageWithOptions($filepath = "Examples/examplePageWithOptions.dxf")
    {
        $cad = new CadMaker();
        $options = [
                "xLength" => 26.8,
                "yLength" => 7.5,
                "marginBottom" => 4.3,
                "marginLeft" => 3.3,
                "marginTop" => 2.3,
                "marginRight" => 1.3,
                ];
        $cad->addPage("A well-defined page", $options);

        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }

    public function exampleMultiplePagesWithDifferentOptions($filepath = "Examples/exampleMultiplePagesWithDifferentOptions.dxf")
    {
        $cad = new CadMaker();
        $options1 = [
                "xLength" => 26.8,
                "yLength" => 7.5,
                "marginBottom" => 4.3,
                "marginLeft" => 3.3,
                "marginTop" => 2.3,
                "marginRight" => 1.3,
        ];

        $options2 = [
                "xLength" => 0.5,
                "yLength" => 0.5,
                "marginBottom" => 0.1,
                "marginLeft" => 0.1,
                "marginTop" => 0.1,
                "marginRight" => 0.1,
        ];

        $options3 = [
                "xLength" => 10.0,
                "yLength" => 15.0,
                "marginBottom" => 0.0,
                "marginLeft" => 2.0,
                "marginTop" => 0.0,
                "marginRight" => 2.0,
        ];

        $cad->addPage("The first page", $options1);
        $cad->addPage("The second page", $options2);
        $cad->addPage("The third page", $options3);

        $dxf = new DxfConverter($cad);
        $dxf->save($filepath);
    }



}