<?php

namespace DxfCreator\tests;

use DxfCreator\DxfBlock;

class DxfBlockTest extends \PHPUnit_Framework_TestCase
{
    public function testBlankDxfBlock()
    {
        $block = new DxfBlock();

        $this->assertEquals("", $block->toString());
    }

    public function testConstructorArg()
    {
        $block = new DxfBlock([["1", "value1"], ["2", "value2"]]);
        $stringExpected = "1\r\nvalue1\r\n2\r\nvalue2\r\n";

        $this->assertEquals($stringExpected, $block->toString());
    }

    public function testVariousInputTypes()
    {
        $block = new DxfBlock();
        $block->add(0, 0);
        $block->add(1, "1.0");
        $block->add(12, 20);
        $block->add(12345, "a string 123");

        $stringExpected = "0\r\n0\r\n1\r\n1.0\r\n12\r\n20\r\n12345\r\na string 123\r\n";
        $this->assertEquals($stringExpected, $block->toString());
    }

    public function testRecursion()
    {
        $block1 = new DxfBlock();
        $block2 = new DxfBlock();
        $block3 = new DxfBlock();
        $block4 = new DxfBlock();

        $block1->add(10, 11);
        $block1->add(4, "fff");
        $block2->add(90, 9);
        $block2->addBlock($block1);
        $block3->addBlock($block1);
        $block3->addBlock($block2);
        $block4->add(5, "hello");
        $block4->addBlock($block3);
        $block4->addBlock($block2);
        $block4->add(1, 1);
        $block4->addBlock($block1);

        $stringExpected = "5\r\nhello\r\n10\r\n11\r\n4\r\nfff\r\n90\r\n9\r\n10\r\n11\r\n"
                . "4\r\nfff\r\n90\r\n9\r\n10\r\n11\r\n4\r\nfff\r\n1\r\n1\r\n10\r\n11\r\n"
                . "4\r\nfff\r\n";

        $this->assertEquals($stringExpected, $block4->toString());
    }
}