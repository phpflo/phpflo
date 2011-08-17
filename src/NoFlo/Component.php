<?php
namespace NoFlo;

class Component implements ComponentInterface
{
    public $inPorts = array();
    public $outPorts = array();
    protected $description = "";

    public function getDescription()
    {
        return $this->description;
    }
}
