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
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Example of how to use a depency injection container with builder.
 *
 * Make leverage of an external DIC to find/factorize your components.
 *
 * @package PhpFlo\Builder
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class ComponentDiFinder implements ComponentBuilderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ComponentDiFinder constructor.
     *
     * @param ContainerInterface $container dependency injection container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param string $component
     * @return ComponentInterface
     * @throws InvalidDefinitionException
     */
    public function build($component)
    {
        $instance = $this->container->get($component);

        if (!empty($instance) && !$instance instanceof ComponentInterface) {
            throw new InvalidDefinitionException(
                "Component {$component} doesn't appear to be a valid PhpFlo component"
            );
        }

        return $instance;
    }
}
