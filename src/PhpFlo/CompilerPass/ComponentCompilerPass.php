<?php

/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpFlo\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ComponentCompilerPass
 *
 * @package PhpFlo\CompilerPass
 * @author Marc Aschmann <maschmann@gmail.com>
 */
class ComponentCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('phpflo.component_registry')) {
            return;
        }

        $definition = $container->findDefinition(
            'phpflo.component_registry'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'phpflo.component'
        );

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                    'add',
                    array(new Reference($id), $attributes['alias'])
                );
            }
        }
    }
}
