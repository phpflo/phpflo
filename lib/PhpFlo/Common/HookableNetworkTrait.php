<?php
/*
 * This file is part of the <package> package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Common;

use PhpFlo\Exception\FlowException;
use PhpFlo\Exception\InvalidTypeException;

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
        'data' => [],
        'connect' => [],
        'disconnect' => [],
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
     * @return $this
     * @throws FlowException
     * @throws InvalidTypeException
     */
    public function hook($event, $alias, \Closure $closure)
    {
        if (!array_key_exists($event, $this->hooks)) {
            throw new InvalidTypeException(
                "Invalid event {$event} given for hook {$alias}! Please use" . array_keys($this->hooks)
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
     * @param SocketInterface $socket
     * @return SocketInterface
     */
    protected function addHooks(SocketInterface $socket)
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