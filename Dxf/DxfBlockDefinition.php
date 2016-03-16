<?php namespace DxfCreator\Dxf;
class DxfBlockDefinition
{
    private $name;
    private $basePoint;
    private $content;
    private $handle;
    private $blockRecordHandle;
    private $endHandle;

    public function __construct($name, $basePoint, $content, $handle, $endHandle, $blockRecordHandle)
    {

        $this->name = $name;
        $this->blockRecordHandle = $blockRecordHandle;
        $this->handle = $handle;
        $this->basePoint = $basePoint;
        $this->content = $content;
        $this->endHandle = $endHandle;
    }

    public function toBlock()
    {
        $block = new DxfBlock();
        $block->add(0, "BLOCK");
        $block->add(5, $this->handle);
        $block->add(330, $this->blockRecordHandle);
        $block->add(100, "AcDbEntity");
        $block->add(8, 0);
        $block->add(100, "AcDbBlockBegin");
        $block->add(2, $this->name);
        $block->add(70, 0);
        $block->add(10, $this->basePoint[0]);
        $block->add(20, $this->basePoint[1]);
        $block->add(30, "0.0");
        $block->add(3, $this->name);
        $block->add(1, "");
        $block->addBlock($this->content);
        $block->add(0, "ENDBLK");
        $block->add(5, $this->endHandle);
        $block->add(330, $this->blockRecordHandle);
        $block->add(100, "AcDbEntity");
        $block->add(8, 0);
        $block->add(100, "AcDbBlockEnd");

        return $block;
    }

}