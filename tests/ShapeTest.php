<?php

namespace DxfCreator\tests;

use DxfCreator\Shape;

class ShapeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider providerTestSetOrigin
     */
    public function testSetOrigin($origin, $expectedResult)
    {
        $shape = new Shape();
        $this->assertEquals($expectedResult, $shape->setOrigin($origin));
    }

    public function providerTestSetOrigin()
    {
        return array(
                array("top left", 1),
                array("middle center", 5),
                array("bottom right", 9),
                array("BOttOM rigHT", 9),
                array("asdf", 1),
                array(0, 1),
                array(6, 6),
                array(31, 1),
                array(34.56, 1),
        );
    }

    /**
     * @dataProvider providerTestSetLineWeight
     */
    public function testLineWeight($weight, $expectedResult)
    {
        $shape = new Shape();
        $this->assertEquals($expectedResult, $shape->setLineWeight($weight));
    }

    public function providerTestSetLineWeight()
    {
        return array(
                array(0, 0),
                array(0.0, 0),
                array(0.0001, 0.05),
                array(0.05, 0.05),
                array(0.1, 0.09),
                array(1, 1.0),
                array(1.3, 1.4),
                array(1.41, 1.4),
                array(2, 2.0),
                array(100000, 2.11),
                array("0.4", 0.4),
                array("foo", 0),
                array(true, 0),
                array(false, 0),
        );
    }

    /**
     * @dataProvider providerTestSetColor
     */
    public function testSetColor($color, $expectedResult)
    {
        $shape = new Shape();
        $this->assertEquals($expectedResult, $shape->setColor($color));
    }

    public function providerTestSetColor()
    {
        return array(
                array("black", 0),
                array("yellow", 2),
                array("white", 7),
                array("WhItE", 7),
                array("asdf", 0),
                array(0, 0),
                array(6, 6),
                array(31, 0),
                array(34.56, 0),
        );
    }
}