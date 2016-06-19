<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo;

use PhpFlo\Exception\PortException;

/**
 * Class PortRegistry
 *
 * @package PhpFlo
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
        $this->attributes = [
            'datatype' => 'all',
            'required' => false,
            'cached' => false,
            'addressable' => false,
        ];
    }

    /**
     * @param string $name
     * @param array $attributes
     * @return $this
     */
    public function add($name, array $attributes)
    {
        switch (true) {
            case (!$this->has($name) && (isset($attributes['addressable']) && false !== $attributes['addressable'])):
                $this->ports[$name] = new ArrayPort(
                    $name,
                    array_merge($this->attributes, $attributes)
                );
                break;
            case (!$this->has($name)):
                $this->ports[$name] = new Port(
                    $name,
                    array_merge($this->attributes, $attributes)
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
     * @return array|Port|ArrayPort
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
