<?php
/*
 * This file is part of the phpflo/phpflo package.
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

/**
 * Example builder for components.
 *
 * This is just an example - every builder implementing the interface could be used to
 * return the component instances.
 * The build method is not static to allow for e.g. easy dependency injection and use
 * as a service.
 *
 * @package PhpFlo
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class ComponentFactory implements ComponentBuilderInterface
{
    /**
     * @param string $component
     * @return ComponentInterface
     * @throws InvalidDefinitionException
     */
    public function build($component)
    {
        if (!class_exists($component) && strpos($component, '\\') === false) {
            $component = "PhpFlo\\Component\\{$component}";
            if (!class_exists($component)) {
                throw new InvalidDefinitionException("Component class {$component} not found");
            }
        }
        $instance = new $component();
        if (!$instance instanceof ComponentInterface) {
            throw new InvalidDefinitionException(
                "Component {$component} doesn't appear to be a valid PhpFlo component"
            );
        }

        return $instance;
    }
}
