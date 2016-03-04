<?php

namespace DxfCreator\tests;

use DxfCreator\DxfConverter;
use DxfCreator\CadMaker;

class DxfConverterTest extends \PHPUnit_Framework_TestCase
{

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