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

use PhpFlo\Exception\ComponentException;
use PhpFlo\Exception\ComponentNotFoundException;

/**
 * Interface ComponentRegistryInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface ComponentRegistryInterface
{
    /**
     * @param string $alias
     * @return ComponentInterface
     * @throws ComponentNotFoundException
     */
    public function get($alias);

    /**
     * @param ComponentInterface $component
     * @param string $alias
     * @return $this
     * @throws ComponentException
     */
    public function add(ComponentInterface $component, $alias);
}
