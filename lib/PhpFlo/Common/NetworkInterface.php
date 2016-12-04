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

use PhpFlo\Graph;

/**
 * Interface NetworkInterface
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
interface NetworkInterface extends BaseNetworkInterface
{
    /**
     * @return bool|\DateInterval
     */
    public function uptime();

    /**
     * @param array $node
     * @return $this
     * @throws InvalidDefinitionException
     */
    public function addNode(array $node);

    /**
     * @param array $node
     * @return $this
     */
    public function removeNode(array $node);

    /**
     * @param string $id
     * @return mixed|null
     */
    public function getNode($id);

    /**
     * @param array $edge
     * @return Network
     * @throws InvalidDefinitionException
     */
    public function addEdge(array $edge);

    /**
     * @param array $edge
     * @return $this
     */
    public function removeEdge(array $edge);

    /**
     * @param Graph $graph
     * @param ComponentBuilderInterface $builder
     * @return Network
     * @throws InvalidDefinitionException
     */
    public static function create(Graph $graph, ComponentBuilderInterface $builder);

    /**
     * Load PhpFlo graph definition from string.
     *
     * @param string $string
     * @param ComponentBuilderInterface $builder
     * @return Network
     * @throws InvalidDefinitionException
     */
    public static function loadString($string, ComponentBuilderInterface $builder);

    /**
     * Load PhpFlo graph definition from file.
     *
     * @param string $file
     * @param ComponentBuilderInterface $builder
     * @return Network
     * @throws InvalidDefinitionException
     */
    public static function loadFile($file, ComponentBuilderInterface $builder);
}
