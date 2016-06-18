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

use Evenement\EventEmitter;

/**
 * Class InternalSocket
 *
 * @package PhpFlo
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class InternalSocket extends EventEmitter implements SocketInterface
{
    /**
     * @var bool
     */
    private $connected;

    /**
     * @var array
     */
    public $from;

    /**
     * @var array
     */
    public $to;

    public function __construct()
    {
        $this->connected = false;
        $this->from = [];
        $this->to = [];
    }

    /**
     * @return string
     */
    public function getId()
    {
        if ($this->from && !$this->to) {
            return "{$this->from['process']['id']}.{$this->from['port']}:ANON";
        }
        if (!$this->from) {
            return "ANON:{$this->to['process']['id']}.{$this->to['port']}";
        }

        return "{$this->from['process']['id']}.{$this->from['port']}:{$this->to['process']['id']}.{$this->to['port']}";
    }

    public function connect()
    {
        $this->connected = true;
        $this->emit('connect', [$this]);
    }

    /**
     * @param string $groupName
     */
    public function beginGroup($groupName)
    {
        $this->emit('beginGroup', [$groupName, $this]);
    }

    /**
     * @param string $groupName
     */
    public function endGroup($groupName)
    {
        $this->emit('endGroup', [$groupName, $this]);
    }

    /**
     * @param mixed $data
     */
    public function send($data)
    {
        $this->emit('data', [$data, $this]);
    }

    public function disconnect()
    {
        $this->connected = false;
        $this->emit('disconnect', [$this]);
    }

    public function isConnected()
    {
        return $this->connected;
    }
}
