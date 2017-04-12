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

use PhpFlo\Interaction\PortRegistry;

/**
 * Class ComponentTrait
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
trait ComponentTrait
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
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @return PortRegistry
     */
    public function inPorts() : PortRegistry
    {
        if (null === $this->inPorts) {
            $this->inPorts = new PortRegistry();
        }

        return $this->inPorts;
    }

    /**
     * @return PortRegistry
     */
    public function outPorts() : PortRegistry
    {
        if (null === $this->outPorts) {
            $this->outPorts = new PortRegistry();
        }

        return $this->outPorts;
    }

    /**
     * @return ComponentInterface
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
