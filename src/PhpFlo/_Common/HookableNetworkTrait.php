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

use PhpFlo\Exception\FlowException;
use PhpFlo\Exception\InvalidTypeException;
use PhpFlo\Common\NetworkInterface as Net;

/**
 * Class HookableNetworkTrait
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
trait HookableNetworkTrait
{

    /**
     * @var array
     */
    protected $hooks = [
        Net::DATA => [],
        Net::CONNECT => [],
        Net::DISCONNECT => [],
        Net::BEGIN_GROUP => [],
        Net::END_GROUP => [],
    ];

    /**
     * Add a closure to an event
     *
     * Accepted events are connect, disconnect and data
     * Closures will be given the
     *
     * @param string $event
     * @param string $alias
     * @param \Closure $closure
     * @return HookableNetworkInterface
     * @throws FlowException
     * @throws InvalidTypeException
     */
    public function hook(string $event, string $alias, \Closure $closure)
    {
        if (!array_key_exists($event, $this->hooks)) {
            throw new InvalidTypeException(
                "Invalid event {$event} given for hook {$alias}! Please use" .
                implode(', ', array_keys($this->hooks))
            );
        }

        if (!empty($this->hooks[$event][$alias])) {
            throw new FlowException(
                "Hook {$alias} is already registered for {$event}!"
            );
        }

        $this->hooks[$event][$alias] = $closure;

        return $this;
    }

    /**
     * Get all defined custom event hooks
     *
     * @return array
     */
    public function hooks() : array
    {
        return $this->hooks;
    }

    /**
     * @param SocketInterface $socket
     * @return SocketInterface
     */
    protected function addHooks(SocketInterface $socket) : SocketInterface
    {
        // examine events
        foreach ($this->hooks as $event => $hooks) {
            // add hooks
            foreach ($hooks as $alias => $hook) {
                // add listener on socket
                $socket->on($event, $hook);
            }
        }

        return $socket;
    }
}
