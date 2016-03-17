<?php namespace DxfCreator\Dxf;
class DxfBlockRecord
{
    private $name;
    private $handle;
    private $refs;

    public function __construct($name, $handle)
    {
        $this->name = $name;
        $this->handle = $handle;
        $this->refs = [];
    }

    public function addRef($handle)
    {
        $this->refs[] = $handle;
    }

    public function toBlock()
    {
        $record = new DxfBlock();
        $record->add(0, "BLOCK_RECORD");
        $record->add(5, $this->handle);
        $record->add(330, 1);
        $record->add(100, "AcDbSymbolTableRecord");
        $record->add(100, "AcDbBlockTableRecord");
        $record->add(2, $this->name);
        $record->add(340, 0);


        if (count($this->refs) > 0){
            $record->add(102, "{BLKREFS");
            foreach ($this->refs as $ref){
                $record->add(331, $ref);
            }
            $record->add(102, "}");
        }

        $record->add(70, 0);
        $record->add(280, 1);
        $record->add(281, 0);

        return $record;
    }
}