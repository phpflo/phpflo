<?php
namespace PhpFlo;

use Evenement\EventEmitter;

/**
 * Class Port
 *
 * @package PhpFlo
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class Port extends EventEmitter
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var null
     */
    private $socket;

    /**
     * @var null
     */
    private $from;

    /**
     * @param string $name
     */
    public function __construct($name = '')
    {
        $this->name = $name;
        $this->socket = null;
        $this->socket = null;
    }

    /**
     * @param SocketInterface $socket
     */
    public function attach(SocketInterface $socket)
    {
        if ($this->socket) {
            throw new \InvalidArgumentException("{$this->name} socket already attached {$this->socket->getId()}");
        }

        $this->socket = $socket;
        $this->attachSocket($socket);
    }

    /**
     * @param SocketInterface $socket
     */
    protected function attachSocket(SocketInterface $socket)
    {
        $this->emit('attach', [$socket]);

        $this->from = $socket->from;

        $socket->on('connect', [$this, 'onConnect']);
        $socket->on('beginGroup', [$this, 'onBeginGroup']);
        $socket->on('data', [$this, 'onData']);
        $socket->on('endGroup', [$this, 'onEndGroup']);
        $socket->on('disconnect', [$this, 'onDisconnect']);
    }

    /**
     * @param SocketInterface $socket
     */
    public function detach(SocketInterface $socket)
    {
        $this->emit('detach', [$socket]);
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

        $this->socket->once('connect', function (SocketInterface $socket) use ($groupName) {
            $socket->beginGroup($groupName);
        });

        $this->socket->connect();
    }

    /**
     * @param $groupName
     */
    public function endGroup($groupName)
    {
        if (!$this->socket) {
            throw new \RuntimeException("This port is not connected");
        }

        $this->socket->endGroup($groupName);
    }

    /**
     * @param mixed $data
     * @return mixed|null
     */
    public function send($data)
    {
        if (!$this->socket) {
            throw new \RuntimeException("This port is not connected");
        }

        if ($this->isConnected()) {
            return $this->socket->send($data);
        }

        $this->socket->once('connect', function (SocketInterface $socket) use ($data) {
            $socket->send($data);
        });

        $this->socket->connect();
    }

    /**
     * @throws \RuntimeException
     */
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

    /**
     * @return bool
     */
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

    /**
     * @param SocketInterface $socket
     */
    public function onConnect(SocketInterface $socket)
    {
        $this->emit('connect', [$socket]);
    }

    /**
     * @param mixed $data
     * @param SocketInterface $socket
     */
    public function onData($data, SocketInterface $socket)
    {
        $this->emit('data', [$data, $socket]);
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onBeginGroup($groupName, SocketInterface $socket)
    {
        $this->emit('beginGroup', [$groupName, $socket]);
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onEndGroup($groupName, SocketInterface $socket)
    {
        $this->emit('endGroup', [$groupName, $socket]);
    }

    /**
     * @param SocketInterface $socket
     */
    public function onDisconnect(SocketInterface $socket)
    {
        $this->emit('disconnect', [$socket]);
    }
}
