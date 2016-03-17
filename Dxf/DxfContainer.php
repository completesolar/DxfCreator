<?php namespace DxfCreator\Dxf;
class DxfContainer extends DxfBlock
{
    private $preBody;
    private $postBody;

    public function __construct(DxfBlock $newPreBody = null, DxfBlock $newPostBody = null)
    {
        $this->preBody = $newPreBody;
        $this->body = [];
        $this->postBody = $newPostBody;
    }

    public function toString()
    {
        $content = "";
        $content .= empty($this->preBody)? "" : $this->preBody->toString();
        $content .= parent::toString();
        $content .= empty($this->postBody)? "" : $this->postBody->toString();
        return $content;
    }

    public function setPreBody(DxfBlock $newPreBody)
    {
        $this->preBody = $newPreBody;
    }

    public function setPostBody(DxfBlock $newPostBody)
    {
        $this->postBody = $newPostBody;
    }
}
