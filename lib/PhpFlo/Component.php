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

use PhpFlo\Common\ComponentInterface;
use PhpFlo\Interaction\PortRegistry;

/**
 * Class Component
 *
 * @package PhpFlo
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class Component implements ComponentInterface
{
    /**
     * @var PortRegistry
     */
    private $inPorts = null;

    /**
     * @var PortRegistry
     */
    private $outPorts = null;

    /**
     * @var string
     */
    protected $description = "";

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return PortRegistry
     */
    public function inPorts()
    {
        if (null == $this->inPorts) {
            $this->inPorts = new PortRegistry();
        }

        return $this->inPorts;
    }

    /**
     * @return PortRegistry
     */
    public function outPorts()
    {
        if (null == $this->outPorts) {
            $this->outPorts = new PortRegistry();
        }

        return $this->outPorts;
    }

    /**
     * @return $this;
     */
    public function shutdown()
    {
        foreach ($this->inPorts()->get() as $port) {
            $port->emit('shutdown', [$port]);
        }

        foreach ($this->outPorts()->get() as $port) {
            $port->emit('shutdown', [$port]);
        }

        return $this;
    }
}
