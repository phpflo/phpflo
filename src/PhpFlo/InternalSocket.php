<?php
namespace PhpFlo;

use Evenement\EventEmitter;

class InternalSocket extends EventEmitter implements SocketInterface
{
    private $connected = false;
    public $from = array();
    public $to = array();

    public function getId()
    {
        if ($this->from && !$this->to) {
            return "{$this->from['process']['id']}.{$this->from['port']}:ANON";
        }
        if (!$this->from) {
            return "ANON:{$this->to['process']['id']}.{$this->to['port']}";
        }

        return "{$this->from['process']['id']}.{$this->from['port']}:{$this->to['process']['id']}.{$this->to['port']}";
    }

    public function connect()
    {
        $this->connected = true;
        $this->emit('connect', array($this));
    }

    /**
     * @param string $groupName
     */
    public function beginGroup($groupName)
    {
        $this->emit('beginGroup', array($groupName, $this));
    }

    /**
     * @param string $groupName
     */
    public function endGroup($groupName)
    {
        $this->emit('endGroup', array($groupName, $this));
    }

    /**
     * @param mixed $data
     */
    public function send($data)
    {
        $this->emit('data', array($data, $this));
    }

    public function disconnect()
    {
        $this->connected = false;
        $this->emit('disconnect', array($this));
    }

    public function isConnected()
    {
        return $this->connected;
    }
}
