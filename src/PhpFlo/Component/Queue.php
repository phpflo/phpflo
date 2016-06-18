<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Component;

use PhpFlo\Component;
use PhpFlo\Port;

/**
 * Component for creating a simple queue of messages.
 *
 * All incomming messages are kept in memory. As soon as the queue reaches the
 * pre-configured size of 100 messages, the current list of messages is send.
 *
 * You can reconfigure the queue size of the component by sending a message to
 * the `size` input port of this component. If the incomming data is not a
 * positive integer, an error message will be send to the `err` out port of this
 * component.
 *
 * @author Marijn Huizendveld <marijn@pink-tie.com>
 */
class Queue extends Component
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
        $this->messages = [];

        $this->inPorts['in'] = new Port();
        $this->inPorts['size'] = new Port();

        $this->outPorts['error'] = new Port();
        $this->outPorts['messages'] = new Port();

        $this->inPorts['in']->on('data', [$this, 'onAppendQueue']);
        $this->inPorts['in']->on('detach', [$this, 'onStreamEnded']);

        $this->inPorts['size']->on('data', [$this, 'onResize']);
    }

    /**
     * @param mixed $data
     */
    public function onAppendQueue($data)
    {
        $this->messages[] = $data;

        $this->sendQueue();
    }

    public function onStreamEnded()
    {
        $this->flushQueue();
    }

    /**
     * @param mixed $data
     */
    public function onResize($data)
    {
        if (!is_int($data) || 0 > $data) {
            $dumped = var_dump($data);

            $this->outPorts['error']->send(
                "Invalid queue size: '{$dumped}'. Queue resize operation expects a positive integer value."
            );
        }

        $this->size = $data;

        $this->sendQueue();
    }

    private function sendQueue()
    {
        if ($this->size <= count($this->messages)) {
            $this->flushQueue();
        }
    }

    private function flushQueue()
    {
        $this->outPorts['messages']->send($this->messages);
        $this->outPorts['messages']->disconnect();

        $this->messages = [];
    }
}
