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
        $socket->on('beginGroup', array($this, 'onBeginGroup'));
        $socket->on('data', array($this, 'onData'));
        $socket->on('endGroup', array($this, 'onEndGroup'));
        $socket->on('disconnect', array($this, 'onDisconnect'));
    }

    public function detach(SocketInterface $socket)
    {
        $this->emit('detach', array($socket));
        $this->from = null;
        $this->socket = null;
    }

    /**
     * @param string $groupName
     * @return null
     * @throws \RuntimeException
     */
    public function beginGroup($groupName)
    {
        if (!$this->socket) {
            throw new \RuntimeException("This port is not connected");
        }

        if ($this->isConnected()) {
            return $this->socket->beginGroup($groupName);
        }

        $this->socket->once('connect', function(SocketInterface $socket) use ($groupName) {
            $socket->beginGroup($groupName);
        });

        $this->socket->connect();
    }

    public function endGroup($groupName)
    {
        if (!$this->socket) {
            throw new \RuntimeException("This port is not connected");
        }

        $this->socket->endGroup($groupName);
    }

    public function send($data)
    {
        if (!$this->socket) {
            throw new \RuntimeException("This port is not connected");
        }

        if ($this->isConnected()) {
            return $this->socket->send($data);
        }

        $this->socket->once('connect', function(SocketInterface $socket) use ($data) {
            $socket->send($data);
        });

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

    /**
     * Checks if port is attached.
     *
     * @return bool
     */
    public function isAttached()
    {
        if (!$this->socket) {
            return false;
        }

        return true;
    }

    public function onConnect(SocketInterface $socket)
    {
        $this->emit('connect', array($socket));
    }

    /**
     * @param mixed $data
     * @param SocketInterface $socket
     */
    public function onData($data, SocketInterface $socket)
    {
        $this->emit('data', array($data, $socket));
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onBeginGroup($groupName, SocketInterface $socket)
    {
        $this->emit('beginGroup', array($groupName, $socket));
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onEndGroup($groupName, SocketInterface $socket)
    {
        $this->emit('endGroup', array($groupName, $socket));
    }

    public function onDisconnect(SocketInterface $socket)
    {
        $this->emit('disconnect', array($socket));
    }
}
