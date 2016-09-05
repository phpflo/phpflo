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
 * Class EventTrait
 *
 * Implements the methods required by EventInterface.
 *
 * @package PhpFlo\Interaction
 * @author Marc Aschmann <maschmann@gmail.com>
 */
trait EventTrait
{
    /**
     * Has propagation stopped?
     *
     * @var bool
     */
    protected $propagationStopped = false;

    public function stopPropagation()
    {
        $this->propagationStopped = true;

        return $this;
    }

    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }
}
