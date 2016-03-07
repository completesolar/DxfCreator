<?php

namespace DxfCreator\tests;

use DxfCreator\DxfConverter;
use DxfCreator\CadMaker;

class DxfConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testTextOnMultiplePages()
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
        $dxf->save('tests/temp/testTextOnMultiplePages.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testTextOnMultiplePages.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testTextOnMultiplePages.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);
    }

    public function testTextStyles()
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $cad->drawText($p1, "Bold", 1, 10, 0.3, null, ["bold" => true]);
        $cad->drawText($p1, "Italic", 2, 9, 0.3, null, ["italic" => true]);
        $cad->drawText($p1, "Underline", 3, 8, 0.3, null, ["underline" => true]);
        $cad->drawText($p1, "Bold, Italic, and Underline", 4, 7, 0.3, null, ["bold" => true, "italic" => true, "underline" => true]);
        $cad->drawText($p1, "The font is Times New Roman", 5, 6, 0.3, null, ["font" => "Times New Roman"]);
        $cad->drawText($p1, '\LThis\l \Ouses\o \C1;the special\C0; \fArial|b0|i1|c0|p34;format\fArial|b0|i0|c0|p34;'
                . ' \fArial|b1|i0|c0|p34;codes\fArial|b0|i0|c0|p34; \H0.7x;\S^ embedded;\H1.4286x; \H0.5x;\Sin/th'
                . 'e; \H2x;\W2;string \fCourier New|b0|i0|c0|p49;\W1;itself', 6, 5, 0.3);
        $cad->drawText($p1, 'See \C5;\Lhttp://docs.autodesk.com/ACD/2010/ENU/AutoCAD%202010%20User%20Documentation/index.h'
                . 'tml?url=WS1a9193826455f5ffa23ce210c4a30acaf-63b9.htm,topicNumber=d0e123454\l \C0;for details on format '
                . 'codes', 0.5, 4, 0.15);

        $dxf = new DxfConverter($cad);
        $dxf->save('tests/temp/testTextStyles.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testTextStyles.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testTextStyles.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);

    }

    public function testDrawTextWithWidthLimit()
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $cad->drawText($p1, "This is text that has a limit on the width of the paragraph.", 2, 5, 0.3, 3);

        $dxf = new DxfConverter($cad);
        $dxf->save('tests/temp/testDrawTextWithWidthLimit.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testDrawTextWithWidthLimit.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testDrawTextWithWidthLimit.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);
    }

    public function testDrawText()
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $cad->drawText($p1, "This is text!", 2, 3, 0.2);

        $dxf = new DxfConverter($cad);
        $dxf->save('tests/temp/testDrawText.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testDrawText.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testDrawText.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);
    }

    /*
     * Note that AutoCAD 'white' (index #7) prints black if on a white background
     */
    public function testRectangleOptions()
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
        $dxf->save('tests/temp/testRectangleOptions.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testRectangleOptions.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testRectangleOptions.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);
    }

    public function testDrawManyRectanglesOnManyPages()
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
        $dxf->save('tests/temp/testDrawManyRectanglesOnManyPages.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testDrawManyRectanglesOnManyPages.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testDrawManyRectanglesOnManyPages.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);
    }

    public function testDrawRectangle()
    {
        $cad = new CadMaker();
        $p1 = $cad->addPage("Page 1");
        $cad->drawRectangle($p1, 0, 0, 3, 3);

        $dxf = new DxfConverter($cad);
        $dxf->save('tests/temp/testDrawRectangle.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testDrawRectangle.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testDrawRectangle.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);
    }

    /*
     * Generates a semi-valid AutoCad file (opens, but layout does not work)
     */
    public function testOneBlankPage()
    {
        $cad = new CadMaker();
        $cad->addPage("Page 1");

        $dxf = new DxfConverter($cad);
        $dxf->save('tests/temp/testOneBlankPage.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testOneBlankPage.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testOneBlankPage.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);
    }

    /*
     * This generates an invalid AutoCad file
     */
    public function testNoPages()
    {
        $cad = new CadMaker();

        $dxf = new DxfConverter($cad);
        $dxf->save('tests/temp/testNoPages.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testNoPages.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testNoPages.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);
    }

    public function testFourValidPages()
    {
        $cad = new CadMaker();
        $cad->addPage("Page 1");
        $cad->addPage("Page 2");
        $cad->addPage("Page 3");
        $cad->addPage("Page 4");

        $dxf = new DxfConverter($cad);
        $dxf->save('tests/temp/testFourValidPages.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testFourValidPages.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testFourValidPages.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);
    }

    public function testPageWithOptions()
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
        $dxf->save('tests/temp/testPageWithOptions.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testPageWithOptions.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testPageWithOptions.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);

    }

    public function testMultiplePagesWithDifferentOptions()
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
        $dxf->save('tests/temp/testMultiplePagesWithDifferentOptions.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testMultiplePagesWithDifferentOptions.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testMultiplePagesWithDifferentOptions.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);

    }



}