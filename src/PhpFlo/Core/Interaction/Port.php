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
use PhpFlo\Common\Exception\InvalidDefinitionException;
use PhpFlo\Common\Exception\PortException;
use PhpFlo\Common\Exception\SocketException;

/**
 * Class Port
 *
 * @package PhpFlo\Core\Interaction
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
final class Port extends AbstractPort implements PortInterface
{
    /**
     * @param SocketInterface $socket
     * @throws InvalidDefinitionException
     * @return PortInterface
     */
    public function attach(SocketInterface $socket): PortInterface
    {
        if ($this->socket) {
            throw new InvalidDefinitionException("{$this->name} socket already attached {$this->socket->getId()}");
        }

        $this->socket = $socket;
        $this->attachSocket($socket);

        return $this;
    }

    /**
     * @param mixed $data
     * @param SocketInterface $socket
     */
    public function onData($data, SocketInterface $socket)
    {
        $this->emit(Net::DATA, [$data, $socket]);
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onBeginGroup(string $groupName, SocketInterface $socket)
    {
        $this->emit(Net::BEGIN_GROUP, [$groupName, $socket]);
    }

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onEndGroup(string $groupName, SocketInterface $socket)
    {
        $this->emit(Net::END_GROUP, [$groupName, $socket]);
    }

    /**
     * @inheritdoc
     */
    public function onShutdown()
    {
        if (null !== $this->socket) {
            $this->socket->shutdown();
            $this->from = null;
            $this->socket = null;
        }

        $this->emit(Net::SHUTDOWN, [$this]);
    }

    /**
     * @throws SocketException
     */
    public function connect()
    {
        if (null !== $this->socket) {
            $this->socket->connect();
        }

        throw new SocketException("No socket available");
    }

    /**
     * @inheritdoc
     */
    public function disconnect()
    {
        if (null !== $this->socket) {
            $this->socket->disconnect();
        }
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        if (null === $this->socket) {
            return false;
        }

        return $this->socket->isConnected();
    }

    /**
     * Checks if port is attached.
     *
     * @return bool
     */
    public function isAttached(): bool
    {
        if (!$this->socket) {
            return false;
        }

        return true;
    }

    /**
     * @param $groupName
     * @throws PortException
     */
    public function endGroup(string $groupName)
    {
        if (null !== $this->socket) {
            $this->socket->endGroup($groupName);
        }

        throw new PortException("This port is not connected");
    }

    /**
     * @param mixed $data
     * @return mixed|null
     * @throws PortException
     */
    public function send($data)
    {
        if (null === $this->socket) {
            throw new PortException("This port is not connected");
        }

        if ($this->isConnected()) {
            return $this->socket->send($data);
        }

        $this->socket->once(Net::CONNECT, function (SocketInterface $socket) use ($data) {
            $socket->send($data);
        });

        $this->socket->connect();

        return $this;
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
     * @throws PortException
     */
    public function beginGroup(string $groupName)
    {
        if (null === $this->socket) {
            throw new PortException("This port is not connected");
        }

        if ($this->isConnected()) {
            return $this->socket->beginGroup($groupName);
        }

        $this->socket->once(Net::CONNECT, function (SocketInterface $socket) use ($groupName) {
            $socket->beginGroup($groupName);
        });

        $this->socket->connect();
    }
}
