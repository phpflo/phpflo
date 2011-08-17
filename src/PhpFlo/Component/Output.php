<?php
namespace PhpFlo\Component;

use PhpFlo\Component;
use PhpFlo\ArrayPort;

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
