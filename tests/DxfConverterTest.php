<?php

namespace DXFWriter\tests;

use DXFWriter\DxfConverter;
use DXFWriter\CadMaker;

class DxfConverterTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Generates a semi-valid AutoCad file (opens, but layout does not work)
     */
    public function testOneBlankPage()
    {
        $cad = new CadMaker();
        $cad->addPage(["name" => "Page 1"]);

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

    /*
     * Generates a valid AutoCad file with four blank pages
     */
    public function testFourValidPages()
    {
        $cad = new CadMaker();
        $cad->addPage(["name" => "Page 1"]);
        $cad->addPage(["name" => "Page 2"]);
        $cad->addPage(["name" => "Page 3"]);
        $cad->addPage(["name" => "Page 4"]);

        $dxf = new DxfConverter($cad);
        $dxf->save('tests/temp/testFourValidPages.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testFourValidPages.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testFourValidPages.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);
    }

    /*
     * Generates a valid AutoCad file, with options taking effect as expected
     */
    public function testPageWithOptions()
    {
        $cad = new CadMaker();
        $options = [
                "name" => "A well-defined page",
                "xLength" => 26.8,
                "yLength" => 7.5,
                "marginBottom" => 4.3,
                "marginLeft" => 3.3,
                "marginTop" => 2.3,
                "marginRight" => 1.3,
                ];
        $cad->addPage($options);

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
                "name" => "The first page",
                "xLength" => 26.8,
                "yLength" => 7.5,
                "marginBottom" => 4.3,
                "marginLeft" => 3.3,
                "marginTop" => 2.3,
                "marginRight" => 1.3,
        ];

        $options2 = [
                "name" => "The second page",
                "xLength" => 0.5,
                "yLength" => 0.5,
                "marginBottom" => 0.1,
                "marginLeft" => 0.1,
                "marginTop" => 0.1,
                "marginRight" => 0.1,
        ];

        $options3 = [
                "name" => "The third page",
                "xLength" => 10.0,
                "yLength" => 15.0,
                "marginBottom" => 0.0,
                "marginLeft" => 2.0,
                "marginTop" => 0.0,
                "marginRight" => 2.0,
        ];

        $cad->addPage($options1);
        $cad->addPage($options2);
        $cad->addPage($options3);

        $dxf = new DxfConverter($cad);
        $dxf->save('tests/temp/testMultiplePagesWithDifferentOptions.dxf');

        $fileGenerated = explode("\r\n", file_get_contents('tests/temp/testMultiplePagesWithDifferentOptions.dxf'));
        $fileExpected = explode("\r\n", file_get_contents('tests/dxf file examples/testMultiplePagesWithDifferentOptions.dxf'));

        $this->assertEquals($fileExpected, $fileGenerated);

    }



}