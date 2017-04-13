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

use PhpFlo\Common\Exception\FlowException;
use PhpFlo\Common\Exception\InvalidTypeException;

/**
 * Interface HookableNetworkInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface HookableNetworkInterface
{
    /**
     * Add a closure to an event
     *
     * Accepted events are connect, disconnect and data
     * Closures will be given the
     *
     * @param string $event
     * @param string $alias
     * @param \Closure $closure
     * @throws FlowException
     * @throws InvalidTypeException
     * @return HookableNetworkInterface
     */
    public function hook(string $event, string $alias, \Closure $closure);

    /**
     * Get all defined custom event hooks
     *
     * @return array
     */
    public function hooks() : array;
}
