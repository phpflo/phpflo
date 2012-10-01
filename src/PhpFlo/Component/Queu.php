<?php

namespace PhpFlo\Component;

use PhpFlo\Component;
use PhpFlo\Port;

/**
 * Component for creating a simple queu of messages.
 *
 * All incomming messages are kept in memory. As soon as the queu reaches the
 * pre-configured size of 100 messages, the current list of messages is send.
 *
 * You can reconfigure the queu size of the component by sending a message to
 * the `size` input port of this component. If the incomming data is not a 
 * positive integer, an error message will be send to the `err` out port of this
 * component.
 *
 * @author Marijn Huizendveld <marijn@pink-tie.com>
 */
class Queu extends Component
{
    /**
     * @var integer
     */
    private $size;

    /**
     * @var array<mixed>
     */
    private $messages;

    public function __construct()
    {
        $this->size = 100;
        $this->messages = array();

        $this->inPorts['in'] = new Port();
        $this->inPorts['size'] = new Port();

        $this->outPorts['err'] = new Port();
        $this->outPorts['messages'] = new Port();

        $this->inPorts['in']->on('data', array($this, 'onAppendQueu'));
        $this->inPorts['in']->on('detach', array($this, 'onStreamEnded'));

        $this->inPorts['size']->on('data', array($this, 'onResize'));
    }

    /**
     * @param mixed $data
     */
    public function onAppendQueu($data)
    {
        $this->messages[] = $data;

        $this->sendQueu();
    }

    public function onStreamEnded()
    {
        $this->flushQueu();
    }

    /**
     * @param mixed $data
     */
    public function onResize($data)
    {
        if (!is_int($data) || 0 > $data) {
            $dumped = var_dump($data);

            $this->outPorts['err']->send("Invalid queu size: '{$dumped}'. Queu resize operation expects a positive integer value.");
        }

        $this->size = $data;

        $this->sendQueu();
    }

    private function sendQueu()
    {
        if ($this->size =< count($this->messages)) {
            $this->flushQueu();
        }
    }

    private function flushQueu()
    {
        $this->outPorts['messages']->send($this->messages);
        $this->outPorts['messages']->disconnect();

        $this->messages = array();
    }
}
