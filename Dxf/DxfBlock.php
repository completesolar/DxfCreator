<?php namespace DxfCreator\Dxf;
class DxfBlock
{
    public $body;

    public function __construct(array $newBody = [])
    {
        $this->body = [];
        foreach ($newBody as $line){
            $this->add($line[0], $line[1]);
        }
    }

    public function add($code, $value)
    {
        $this->body[] = [$code, $value];
    }

    public function addBlock(DxfBlock $block)
    {
        if (!is_null($block)){
            $this->body[] = $block;
        }
    }

    public function toString()
    {
        $content = "";

        if (empty($this->body))
            return "";

        foreach($this->body as $entry)
        {
            if (gettype($entry) == "object"){
                $content.= $entry->toString();
            } else {
                $content .= "$entry[0]\r\n$entry[1]\r\n";
            }
        }
        return $content;
    }

    public function addArray($lines, $alternating = false)
    {
        if ($alternating){
            for ($i = 0; $i < count($lines); $i = $i+2){
                $this->body[] = [trim($lines[$i]), trim($lines[$i+1])];
            }
        } else {
            foreach ($lines as $line){
                $this->add(trim($line[0]), trim($line[1]));
            }
        }
    }
}
