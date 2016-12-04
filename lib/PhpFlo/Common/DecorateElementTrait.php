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

/**
 * Class DecorateElementTrait
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
trait DecorateElementTrait
{
    private $decorators = [];

    public function decorate()
    {

    }

    public function decorators()
    {

    }

    /**
     * Add a
     *
     * @param string $alias name of decorator, e.g. classname
     * @param string $class full class path
     * @return $this
     */
    public function decorator($alias, $class)
    {
        if (!isset($this->decorators[$alias])) {
            $this->decorators[$alias] = $class;
        }

        return $this;
    }
}
