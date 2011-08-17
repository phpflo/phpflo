<?php
namespace PhpFlo;

use Evenement\EventEmitter;

class Port extends EventEmitter
{
    private $name = "";
    private $socket = null;
    private $from = null;

    public function __construct($name = '')
    {
        $this->name = $name;
    }

    public function attach(SocketInterface $socket)
    {
        if ($this->socket) {
            throw new \InvalidArgumentException("{$this->name} socket already attached {$this->socket->getId()}");
        }

        $this->socket = $socket;
        $this->attachSocket($socket);
    }

    protected function attachSocket(SocketInterface $socket)
    {
        $this->emit('attach', array($socket));

        $this->from = $socket->from;

        $socket->on('connect', array($this, 'onConnect'));
        $socket->on('data', array($this, 'onData'));
        $socket->on('disconnect', array($this, 'onDisconnect'));
    }

    public function detach(SocketInterface $socket)
    {
        $this->emit('detach', array($socket));
        $this->from = null;
        $this->socket = null; 
    }

    public function send($data)
    {
        if (!$this->socket) {
            throw new \RuntimeException("This port is not connected");
        }

        if ($this->isConnected()) {
            return $this->socket->send($data);
        }

        $sendOnce = function(SocketInterface $socket) use ($data, &$sendOnce)
        {
            $socket->send($data);
            $socket->removeListener('connect', $sendOnce);
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

    public function onConnect(SocketInterface $socket)
    {
        $this->emit('connect', array($socket));
    }

    public function onData($data)
    {
        $this->emit('data', array($data));
    }

    public function onDisconnect(SocketInterface $socket)
    {
        $this->emit('disconnect', array($socket));
    }
}
