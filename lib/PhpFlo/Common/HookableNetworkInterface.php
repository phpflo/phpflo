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

use PhpFlo\Exception\FlowException;
use PhpFlo\Exception\InvalidTypeException;

/**
 * Interface HookableNetworkInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface HookableNetworkInterface extends NetworkInterface
{
    /**
     * Add a closure to an event
     *
     * Accepted events are connect, disconnect and data
     * Closures will be given the
     *
     * @param string $alias
     * @param string $event
     * @param \Closure $closure
     * @throws FlowException
     * @throws InvalidTypeException
     * @return $this
     */
    public function hook($alias, $event, \Closure $closure);
}
