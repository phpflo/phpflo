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

namespace PhpFlo\Core;

use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\ComponentRegistryInterface;
use PhpFlo\Common\Exception\ComponentException;
use PhpFlo\Common\Exception\ComponentNotFoundException;

/**
 * Class ComponentRegistry
 *
 * @package PhpFlo\Core
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class ComponentRegistry implements ComponentRegistryInterface
{
    /**
     * @var array
     */
    private $references;

    public function __construct()
    {
        $this->references = [];
    }

    /**
     * @param string $alias
     * @return ComponentInterface
     * @throws ComponentNotFoundException
     */
    public function get(string $alias): ComponentInterface
    {
        if (array_key_exists($alias, $this->references)) {
            return $this->references[$alias];
        } else {
            throw new ComponentNotFoundException("Could not find component {$alias} in registry.");
        }
    }

    /**
     * @param ComponentInterface $component
     * @param string $alias
     * @return ComponentRegistryInterface
     * @throws ComponentException
     */
    public function add(ComponentInterface $component, string $alias): ComponentRegistryInterface
    {
        if (!array_key_exists($alias, $this->references)) {
            $this->references[$alias] = $component;
        } else {
            throw new ComponentException("The component {$alias} is already registered.");
        }

        return $this;
    }
}
