<?php
namespace NoFlo;

use Evenement\EventEmitter;

class Port extends EventEmitter
{
    private $name = "";
    private $socket = null;
    private $from = null;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function attach(SocketInterface $socket)
    {
        if ($this->socket) {
            throw new \InvalidArgumentException("{$this->name}: socket already attached {$this->socket->getId()}");
        }

        $this->socket = $socket;
        $this->attachSocket($socket);
    }

    protected function attachSocket(SocketInterface $socket)
    {
        $this->emit('attach', array('socket' => $socket));

        $this->from = $socket->from;

        $socket->on('connect', array($this, 'onConnect'));
        $socket->on('data', array($this, 'onData'));
        $socket->on('disconnect', array($this, 'onDisconnect'));
    }

    public function detach(SocketInterface $socket)
    {
        $this->emit('detach', array('socket' => $socket));
        $this->from = null;
        $this->socket = null; 
    }

    public function send($data)
    {
        if ($this->isConnected()) {
            return $this->socket->send($data);
        }

        $sendOnce = function($socketData) use ($data, &$sendOnce)
        {
            $socketData['socket']->send($data);
            $socketData['socket']->removeListener('connect', $sendOnce);
        };

        $this->socket->on('connect', $sendOnce);
        $this->socket->connect();
    }

    public function connect()
    {
        if (!$this->socket) {
            throw new \RuntimeException("No socket available");
        }
        $this->socket->connect();
    }

    public function disconnect()
    {
        if (!$this->socket) {
            return;
        }

        $this->socket->disconnect();
    }

    public function isConnected()
    {
        if (!$this->socket) {
            return false;
        }
        return $this->socket->isConnected();
    }

    public function onConnect(array $data)
    {
        $this->emit('connect', $data);
    }

    public function onData(array $data)
    {
        $this->emit('data', $data);
    }

    public function onDisconnect(array $data)
    {
        $this->emit('disconnect', $data);
    }
}
