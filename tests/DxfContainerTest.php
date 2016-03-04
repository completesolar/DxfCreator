<?php

namespace DxfCreator\tests;

use DxfCreator\DxfBlock;
use DxfCreator\DxfContainer;

class DxfContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testBlankContainer()
    {
        $dxf = new DxfContainer();
        $this->assertEquals("", $dxf->toString());
    }

    public function testOnlyBody()
    {
        $dxf = new DxfContainer();
        $dxf->add(10, 10);
        $dxf->add(0, "monkey");

        $stringExpected = "10\r\n10\r\n0\r\nmonkey\r\n";
        $this->assertEquals($stringExpected, $dxf->toString());
    }

    public function testOnlyPreBody()
    {
        $pre = new DxfBlock();
        $pre->add(4, "0.0");
        $pre->add(10, 10);
        $dxf = new DxfContainer($pre);

        $stringExpected = "4\r\n0.0\r\n10\r\n10\r\n";
        $this->assertEquals($stringExpected, $dxf->toString());
    }

    public function testOnlyPostBody()
    {
        $post = new DxfBlock();
        $post->add(4, "0.0");
        $post->add(10, 10);
        $dxf = new DxfContainer(null, $post);

        $stringExpected = "4\r\n0.0\r\n10\r\n10\r\n";
        $this->assertEquals($stringExpected, $dxf->toString());
    }

    public function testContainerWithAllParts()
    {
        $pre = new DxfBlock();
        $pre->add(4, "0.0");
        $pre->add(10, 10);
        $post = new DxfBlock();
        $post->add(11, "asdf");
        $post->add(0, 0);
        $dxf = new DxfContainer($pre, $post);
        $dxf->add(1, "1.2.3");
        $dxf->add(50, 123);
        $dxf->addBlock($pre);

        $stringExpected = "4\r\n0.0\r\n10\r\n10\r\n"
                . "1\r\n1.2.3\r\n50\r\n123\r\n4\r\n"
                . "0.0\r\n10\r\n10\r\n11\r\nasdf\r\n0\r\n0\r\n";

        $this->assertEquals($stringExpected, $dxf->toString());
    }

}