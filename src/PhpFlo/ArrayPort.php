<?php
namespace PhpFlo;

class ArrayPort extends Port
{
    private $sockets = array();

    public function attach(SocketInterface $socket)
    {
        $this->sockets[] = $socket;
        $this->attachSocket($socket);
    }

    public function detach(SocketInterface $socket)
    {
        $index = array_search($socket, $this->sockets);
        if ($index === false) {
            return;
        }

        $this->emit('detach', array($socket));
        $this->sockets = array_splice($this->sockets, $index, 1);
    }

    public function send($data, $socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new \InvalidArgumentException("No socket {$socketId} connected");
        }

        if ($this->isConnected($socketId)) {
            return $this->sockets[$socketId]->send($data);
        }

        $this->sockets[$socketId]->once('connect', function(SocketInterface $socket) use ($data)
        {
            $socket->send($data);
        }); 
        $this->sockets[$socketId]->connect();
    }

    public function connect($socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new \InvalidArgumentException("No socket {$socketId} connected");
        }

        $this->sockets[$socketId]->connect();
    }

    public function disconnect($socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            return;
        }

        $this->sockets[$socketId]->disconnect();
    }

    public function isConnected($socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            return false;
        }

        return $this->sockets[$socketId]->isConnected();
    }
}
