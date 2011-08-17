<?php
namespace NoFlo\Component;

use NoFlo\Component;
use NoFlo\Port;

class SplitStr extends Component
{
    private $delimiterString = "\n";
    private $string = "";

    public function __construct()
    {
        $this->inPorts['in'] = new Port();
        $this->inPorts['delimiter'] = new Port();
        $this->outPorts['out'] = new Port();

        $this->inPorts['delimiter']->on('data', array($this, 'setDelimiter'));
        $this->inPorts['in']->on('data', array($this, 'appendString'));
        $this->inPorts['in']->on('disconnect', array($this, 'splitString'));
    }

    public function setDelimiter($data)
    {
        $this->delimiterString = $data;
    }

    public function appendString($data)
    {
        $this->string .= $data;
    }

    public function splitString()
    {
        $parts = explode($this->delimiterString, $this->string);
        foreach ($parts as $part) {
            $this->outPorts['out']->send($part);
        }
        $this->outPorts['out']->disconnect();
        $this->string = "";
    }
}
