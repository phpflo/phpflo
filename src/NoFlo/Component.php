<?php
namespace NoFlo;

class Component
{
    protected $inPorts = array();
    protected $outPorts = array();
    protected $description = "";

    public function getDescription()
    {
        return $this->description;
    }
}
