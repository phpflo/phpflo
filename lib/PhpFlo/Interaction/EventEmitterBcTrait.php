<?php
/*
 * This file is part of the <package> package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Interaction;

use League\Event\EventInterface;
use League\Event\ListenerInterface;

/**
 * Class EventEmitterBcTrait
 *
 * Add BC methods to Emitter classes.
 *
 * @package PhpFlo\Interaction
 * @author Marc Aschmann <maschmann@gmail.com>
 */
trait EventEmitterBcTrait
{

    /**
     * BC method for evenement.
     *
     * @param EventInterface $event
     * @param ListenerInterface $listener
     * @param int $priority
     * @return $this
     */
    public function on($event, $listener, $priority = self::P_NORMAL)
    {
        $this->addListener($event, $listener, $priority);

        return $this;
    }
}
