<?php
namespace NoFlo\Component;

use NoFlo\Component;
use NoFlo\Port;

class Counter extends Component
{
    private $count = null;

    public function __construct()
    {
        $this->inPorts['in'] = new Port();
        $this->outPorts['count'] = new Port();

        $this->inPorts['in']->on('data', array($this, 'appendCount'));
        $this->inPorts['in']->on('disconnect', array($this, 'sendCount'));
    }

    public function appendCount($data)
    {
        if (is_null($this->count)) {
            $this->count = 0;
        }
        $this->count++;
    }

    public function sendCount()
    {
        $this->outPorts['count']->send($this->count);
        $this->outPorts['count']->disconnect();
        $this->count = null;
    }
}
