<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo;

/**
 * Interface SocketInterface
 *
 * @package PhpFlo
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
interface SocketInterface
{
    /**
     * @return string
     */
    public function getId();

    public function connect();

    /**
     * @param mixed $data
     */
    public function send($data);

    public function disconnect();

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
}
