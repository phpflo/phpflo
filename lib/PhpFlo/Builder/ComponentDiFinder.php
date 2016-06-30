<?php
/*
 * This file is part of the <package> package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo\Builder;


use PhpFlo\Common\ComponentBuilderInterface;
use PhpFlo\Common\ComponentInterface;
use PhpFlo\Exception\InvalidDefinitionException;

class ComponentDiFinder implements ComponentBuilderInterface
{

    /**
     * @param string $component
     * @return ComponentInterface
     * @throws InvalidDefinitionException
     */
    public static function build($component)
    {
        // TODO: Implement build() method.
    }
}
