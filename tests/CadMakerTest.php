<?php

namespace DXFWriter\tests;

use DXFWriter\CadMaker;
use DXFWriter\Page;

class CadMakerTest extends \PHPUnit_Framework_TestCase
{   
    public function testAddPage(){
        $pageExpected = new Page();
        
        $cad = new CadMaker();
        $pageGenerated = $cad->pages[$cad->addPage()];        
        $this->assertEquals($pageExpected, $pageGenerated);
    }
    
    public function testSavingNewEmptyFile(){
        $cad = new CadMaker();
        $cad->save('tests/temp/newEmptyFile');

        $fileGenerated = explode("\n", file_get_contents('tests/temp/newEmptyFile.dxf'));
        $fileExpected = explode("\n", file_get_contents('tests/dxf file examples/newEmptyFile.dxf'));
        
        $this->assertEquals($fileExpected, $fileGenerated);
    }    
    
    protected function setUp(){
        
    }
}