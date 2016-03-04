<?php namespace DxfCreator;
class DxfBlock
{
    private $body;

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
        $this->body[] = $block;
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
}
