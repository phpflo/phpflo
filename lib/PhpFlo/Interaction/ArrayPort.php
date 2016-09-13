<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Interaction;

use PhpFlo\Common\PortInterface;
use PhpFlo\Common\SocketInterface;
use PhpFlo\Exception\SocketException;

/**
 * Class ArrayPort
 *
 * @package PhpFlo\Interaction
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
final class ArrayPort extends AbstractPort implements PortInterface
{
    /**
     * @var array
     */
    private $sockets = [];

    /**
     * @param SocketInterface $socket
     * @return $this
     */
    public function attach(SocketInterface $socket)
    {
        $this->sockets[] = $socket;
        $this->attachSocket($socket);

        return $this;
    }

    /**
     * @param SocketInterface $socket
     */
    public function detach(SocketInterface $socket)
    {
        $index = array_search($socket, $this->sockets);
        if ($index === false) {
            return;
        }

        $this->emit('detach', [$socket]);
        $this->sockets = array_splice($this->sockets, $index, 1);
    }

    /**
     * @param string $groupName
     * @param int $socketId
     * @return null
     * @throws SocketException
     */
    public function beginGroup($groupName, $socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new SocketException("No socket {$socketId} connected");
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
     * @param string $groupName
     * @param int $socketId
     * @throws SocketException
     */
    public function endGroup($groupName, $socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new SocketException("No socket {$socketId} connected");
        }

        $this->sockets[$socketId]->endGroup($groupName);
    }

    /**
     * @param mixed $data
     * @param int $socketId
     * @return mixed
     * @throws SocketException
     */
    public function send($data, $socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new SocketException("No socket {$socketId} connected");
        }

        if ($this->isConnected($socketId)) {
            return $this->sockets[$socketId]->send($data);
        }

        $this->sockets[$socketId]->once('connect', function(SocketInterface $socket) use ($data) {
            $socket->send($data);
        });
        $this->sockets[$socketId]->connect();
    }

    /**
     * @param int $socketId
     * @throws SocketException
     */
    public function connect($socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new SocketException("No socket {$socketId} connected");
        }

        $this->sockets[$socketId]->connect();
    }

    /**
     * @param int $socketId
     */
    public function disconnect($socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            return;
        }

        $this->sockets[$socketId]->disconnect();
    }

    /**
     * @param int $socketId
     * @return bool
     */
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
     * return int[]
     */
    public function listAttached()
    {
        return array_keys($this->sockets);
    }

    /**
     * @param mixed $data
     * @param SocketInterface $socket
     */
    public function onData($data, SocketInterface $socket)
    {
        $this->emit('data', [$data, $this->getSocketIndex($socket)]);
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onBeginGroup($groupName, SocketInterface $socket)
    {
        $this->emit('begin.group', [$groupName, $this->getSocketIndex($socket)]);
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onEndGroup($groupName, SocketInterface $socket)
    {
        $this->emit('end.group', [$groupName, $this->getSocketIndex($socket)]);
    }

    /**
     * @return $this
     */
    public function onShutdown()
    {
        foreach ($this->sockets as $id => $socket) {
            $this->disconnect($id);
        }

        return $this;
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
