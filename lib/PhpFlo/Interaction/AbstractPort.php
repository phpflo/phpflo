<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Interaction;

use Evenement\EventEmitter;
use PhpFlo\Common\SocketInterface;

/**
 * Class AbstractPort
 *
 * @package PhpFlo\Interaction
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class AbstractPort extends EventEmitter
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var null
     */
    protected $socket;

    /**
     * @var null
     */
    protected $from;

    /**
     * @param string $name
     * @param array $attributes
     */
    public function __construct($name, array $attributes)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->socket = null;
        $this->from = null;
    }

    /**
     * @param SocketInterface $socket
     */
    public function onConnect(SocketInterface $socket)
    {
        $this->emit('connect', [$socket]);
    }

    /**
     * @param SocketInterface $socket
     */
    public function onDisconnect(SocketInterface $socket)
    {
        $this->emit('disconnect', [$socket]);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getAttribute($name)
    {
        $attribute = null;

        if (array_key_exists($name, $this->attributes)) {
            $attribute = $this->attributes[$name];
        }

        return $attribute;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
}
