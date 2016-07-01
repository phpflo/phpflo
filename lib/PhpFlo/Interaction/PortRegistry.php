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

use PhpFlo\Exception\PortException;

/**
 * Class PortRegistry
 *
 * @package PhpFlo\Interaction
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class PortRegistry implements \Iterator
{
    /**
     * @var array
     */
    private $ports;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var int
     */
    private $position;

    public function __construct()
    {
        $this->position = 0;
        $this->ports = [];
    }

    /**
     * @param string $name
     * @param array $attributes
     * @return $this
     * @throws PortException
     */
    public function add($name, array $attributes)
    {
        switch (true) {
            case (!$this->has($name) && (isset($attributes['addressable']) && false !== $attributes['addressable'])):
                $this->ports[$name] = new ArrayPort(
                    $name,
                    $attributes
                );
                break;
            case (!$this->has($name)):
                $this->ports[$name] = new Port(
                    $name,
                    $attributes
                );
                break;
            default:
                throw new PortException("The port {$name} already exists!");
        }

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        $hasPort = false;

        if (array_key_exists($name, $this->ports)) {
            $hasPort = true;
        }

        return $hasPort;
    }

    /**
     * Return one or all ports.
     *
     * @param string $name
     * @return array|ArrayPort|Port
     * @throws PortException
     */
    public function get($name = '')
    {
        switch (true) {
            case ('' == $name):
                $result = $this->ports;
                break;
            case $this->has($name):
                $result = $this->ports[$name];
                break;
            default:
                throw new PortException("The port {$name} does not exist!");
        }

        return $result;
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        if ($this->has($name)) {
            $this->ports[$name] = null;
            unset($this->ports[$name]);
        }
    }

    /**
     * @param string $name
     * @return Port|ArrayPort
     * @throws PortException
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    function rewind() {
        $this->position = 0;
    }

    function current() {
        return $this->ports[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return isset($this->ports[$this->position]);
    }
}
