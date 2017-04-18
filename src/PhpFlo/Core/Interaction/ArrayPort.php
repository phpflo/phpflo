<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PhpFlo\Core\Interaction;

use PhpFlo\Common\NetworkInterface as Net;
use PhpFlo\Common\PortInterface;
use PhpFlo\Common\SocketInterface;
use PhpFlo\Common\Exception\SocketException;

/**
 * Class ArrayPort
 *
 * @package PhpFlo\Core\Interaction
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
     * @return PortInterface
     */
    public function attach(SocketInterface $socket): PortInterface
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

        $this->emit(Net::DETACH, [$socket]);
        $this->sockets = array_splice($this->sockets, $index, 1);
    }

    /**
     * @param string $groupName
     * @param int $socketId
     * @return null
     * @throws SocketException
     */
    public function beginGroup(string $groupName, int $socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new SocketException("No socket {$socketId} connected");
        }

        if ($this->isConnected($socketId)) {
            return $this->sockets[$socketId]->beginGroup($groupName);
        }

        $this->sockets[$socketId]->once(Net::CONNECT, function (SocketInterface $socket) use ($groupName) {
            $socket->beginGroup($groupName);
        });
        $this->sockets[$socketId]->connect();
    }

    /**
     * @param string $groupName
     * @param int $socketId
     * @throws SocketException
     */
    public function endGroup(string $groupName, int $socketId = 0)
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
    public function send($data, int $socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new SocketException("No socket {$socketId} connected");
        }

        if ($this->isConnected($socketId)) {
            return $this->sockets[$socketId]->send($data);
        }

        $this->sockets[$socketId]->once(Net::CONNECT, function (SocketInterface $socket) use ($data) {
            $socket->send($data);
        });
        $this->sockets[$socketId]->connect();
    }

    /**
     * @param int $socketId
     * @throws SocketException
     */
    public function connect(int $socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            throw new SocketException("No socket {$socketId} connected");
        }

        $this->sockets[$socketId]->connect();
    }

    /**
     * @param int $socketId
     * @return $this|void
     */
    public function disconnect(int $socketId = 0)
    {
        if (!isset($this->sockets[$socketId])) {
            return;
        }

        $this->sockets[$socketId]->disconnect();

        return $this;
    }

    /**
     * @param int $socketId
     * @return bool
     */
    public function isConnected(int $socketId = 0): bool
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
    public function isAttached(int $socketId = 0): bool
    {
        if (!isset($this->sockets[$socketId])) {
            return false;
        }

        return true;
    }

    /**
     * return int[]
     */
    public function listAttached(): array
    {
        return array_keys($this->sockets);
    }

    /**
     * @param mixed $data
     * @param SocketInterface $socket
     */
    public function onData($data, SocketInterface $socket)
    {
        $this->emit(Net::DATA, [$data, $this->getSocketIndex($socket)]);
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onBeginGroup(string $groupName, SocketInterface $socket)
    {
        $this->emit(Net::BEGIN_GROUP, [$groupName, $this->getSocketIndex($socket)]);
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onEndGroup(string $groupName, SocketInterface $socket)
    {
        $this->emit(Net::END_GROUP, [$groupName, $this->getSocketIndex($socket)]);
    }

    /**
     * @return $this
     */
    public function onShutdown()
    {
        /** @var InternalSocket $subSocket */
        foreach ($this->sockets as $subSocket) {
            $subSocket->shutdown();
        }
    }

    /**
     * @param SocketInterface $socket
     * @return int
     */
    protected function getSocketIndex(SocketInterface $socket): int
    {
        $index = 0;
        foreach ($this->sockets as $subSocket) {
            if ($subSocket->getId() == $socket->getId()) {
                break;
            }
            $index++;
        }

        return $index;
    }
}
