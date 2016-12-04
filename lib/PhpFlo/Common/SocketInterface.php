<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Common;

/**
 * Interface SocketInterface
 *
 * @package PhpFlo\Common
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
interface SocketInterface
{
    /**
     * SocketInterface constructor.
     *
     * @param array $from
     * @param array $to
     */
    public function __construct(array $from = [], array $to = []);

    /**
     * @return string
     */
    public function getId();

    /**
     * Emits connect event.
     */
    public function connect();

    /**
     * Send data from connected out port to connected in port.
     * Emits data event.
     *
     * @param mixed $data
     */
    public function send($data);

    /**
     * Disconnect port, emit disconnect event.
     */
    public function disconnect();

    /**
     * Disconnect socket, emit shutdown event.
     */
    public function shutdown();

    /**
     * @return boolean
     */
    public function isConnected();

    /**
     * @param string $groupName
     */
    public function beginGroup($groupName);

    /**
     * @param string $groupName
     */
    public function endGroup($groupName);

    /**
     * @param array $from
     * @return mixed
     */
    public function from(array $from = []);

    /**
     * @param array $to
     * @return $this|array
     */
    public function to(array $to = []);
}
