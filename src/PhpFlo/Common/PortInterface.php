<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace PhpFlo\Common;

use PhpFlo\Common\Exception\InvalidDefinitionException;
use PhpFlo\Common\Exception\InvalidTypeException;
use PhpFlo\Common\Exception\PortException;
use PhpFlo\Common\Exception\SocketException;

/**
 * Interface PortInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface PortInterface
{
    /**
     * @param SocketInterface $socket
     */
    public function onConnect(SocketInterface $socket);

    /**
     * @param SocketInterface $socket
     */
    public function onDisconnect(SocketInterface $socket);

    /**
     * @return array
     */
    public function getAttributes() : array ;

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getAttribute(string $name);

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param SocketInterface $socket
     * @throws InvalidDefinitionException
     * @return PortInterface
     */
    public function attach(SocketInterface $socket) : PortInterface;

    /**
     * @param mixed $data
     * @param SocketInterface $socket
     */
    public function onData($data, SocketInterface $socket);

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onBeginGroup(string $groupName, SocketInterface $socket);

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onEndGroup(string $groupName, SocketInterface $socket);

    /**
     * Callback for shutdown event, disconnects and detaches port and sockets.
     */
    public function onShutdown();

    /**
     * Callback for detach event.
     */
    public function onDetach();

    /**
     * @throws SocketException
     */
    public function connect();

    /**
     * Emits disconnect event.
     */
    public function disconnect();

    /**
     * @return bool
     */
    public function isConnected() : bool;

    /**
     * Checks if port is attached.
     *
     * @return bool
     */
    public function isAttached() : bool;

    /**
     * @param string $groupName
     * @throws PortException
     */
    public function endGroup(string $groupName);

    /**
     * @param mixed $data
     * @return mixed|null
     * @throws InvalidTypeException
     */
    public function send($data);

    /**
     * @param SocketInterface $socket
     */
    public function detach(SocketInterface $socket);

    /**
     * @param string $groupName
     * @return null
     * @throws PortException
     */
    public function beginGroup(string $groupName);
}
