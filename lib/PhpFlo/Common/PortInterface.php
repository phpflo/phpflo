<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Common;

use PhpFlo\Exception\InvalidDefinitionException;
use PhpFlo\Exception\InvalidTypeException;
use PhpFlo\Exception\PortException;
use PhpFlo\Exception\SocketException;

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
    public function getAttributes();

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getAttribute($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param SocketInterface $socket
     * @throws InvalidDefinitionException
     */
    public function attach(SocketInterface $socket);

    /**
     * @param mixed $data
     * @param SocketInterface $socket
     */
    public function onData($data, SocketInterface $socket);

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onBeginGroup($groupName, SocketInterface $socket);

    /**
     * @param string $groupName
     * @param SocketInterface $socket
     */
    public function onEndGroup($groupName, SocketInterface $socket);

    /**
     * @throws SocketException
     */
    public function connect();

    public function disconnect();

    /**
     * @return bool
     */
    public function isConnected();

    /**
     * Checks if port is attached.
     *
     * @return bool
     */
    public function isAttached();

    /**
     * @param $groupName
     * @throws PortException
     */
    public function endGroup($groupName);

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
    public function beginGroup($groupName);
}
