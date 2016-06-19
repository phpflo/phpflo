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

use PhpFlo\Exception\FlowException;

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
    private $_inPorts = null;

    /**
     * @var PortRegistry
     */
    private $_outPorts = null;

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
        if (null == $this->_inPorts) {
            $this->_inPorts = new PortRegistry();
        }

        return $this->_inPorts;
    }

    /**
     * @return PortRegistry
     */
    public function outPorts()
    {
        if (null == $this->_outPorts) {
            $this->_outPorts = new PortRegistry();
        }

        return $this->_outPorts;
    }
}
