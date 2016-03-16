<?php

namespace DxfCreator\tests;

use DxfCreator\Drawing;
use DxfCreator\Page;

class DrawingTest extends \PHPUnit_Framework_TestCase
{
    public function testAddPage(){
        $pageExpected = new Page("Page 1");

        $cad = new Drawing();
        $pageGenerated = $cad->pages[$cad->addPage("Page 1")];
        $this->assertEquals($pageExpected, $pageGenerated);
    }

    protected function setUp(){

    }
}