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

    /**
     * @param string $groupName
     * @param int $socketId
     * @return null
     * @throws \InvalidArgumentException
     */
    public function beginGroup($groupName, $socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new \InvalidArgumentException("No socket {$socketId} connected");
        }

        if ($this->isConnected($socketId)) {
            return $this->sockets[$socketId]->beginGroup($groupName);
        }

        $this->sockets[$socketId]->once('connect', function(SocketInterface $socket) use ($groupName) {
            $socket->beginGroup($groupName);
        });
        $this->sockets[$socketId]->connect();
    }

    /**
     * @param int $socketId
     * @throws \InvalidArgumentException
     */
    public function endGroup($socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new \InvalidArgumentException("No socket {$socketId} connected");
        }

        $this->sockets[$socketId]->endGroup();
    }

    public function send($data, $socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new \InvalidArgumentException("No socket {$socketId} connected");
        }

        if ($this->isConnected($socketId)) {
            return $this->sockets[$socketId]->send($data);
        }

        $this->sockets[$socketId]->once('connect', function(SocketInterface $socket) use ($data) {
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

    /**
     * Checks if socket is attached.
     *
     * @param int $socketId
     * @return bool
     */
    public function isAttached($socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $data
     * @param SocketInterface $socket
     */
    public function onData($data, SocketInterface $socket)
    {
        $this->emit('data', array($data, $this->getSocketIndex($socket)));
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onBeginGroup($groupName, SocketInterface $socket)
    {
        $this->emit('beginGroup', array($groupName, $this->getSocketIndex($socket)));
    }

    /**
     * @param SocketInterface $socket
     */
    public function onEndGroup(SocketInterface $socket)
    {
        $this->emit('endGroup', array($this->getSocketIndex($socket)));
    }

    /**
     * 
     * @param SocketInterface $socket
     * @return int
     */
    protected function getSocketIndex($socket)
    {
        $index = 0;

        foreach($this->sockets as $subSocket) {
            if($subSocket->getId() == $socket->getId()) {
                break;
            }
            
            $index++;
        }

        return $index;
    }
}
