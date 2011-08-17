<?php
namespace NoFlo\Component;

use NoFlo\Component;
use NoFlo\ArrayPort;

class Output extends Component
{
    public function __construct()
    {
        $this->inPorts['in'] = new ArrayPort();

        $this->inPorts['in']->on('data', array($this, 'displayData'));
    }

    public function displayData($data)
    {
        echo "{$data}\n";
    }
}
