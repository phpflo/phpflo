<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpFlo;

use Evenement\EventEmitter;
use PhpFlo\Common\DefinitionInterface;
use PhpFlo\Exception\InvalidDefinitionException;
use PhpFlo\Fbp\FbpParser;
use PhpFlo\Loader\Loader;

/**
 * Analyzes and creates definitions from flow graph file.
 *
 * @package PhpFlo
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class Graph extends EventEmitter
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    public $nodes;

    /**
     * @var array
     */
    public $edges;

    /**
     * @var array
     */
    public $initializers;

    /**
     * @var DefinitionInterface
     */
    private $definition;

    /**
     * @param DefinitionInterface $definition
     */
    public function __construct(DefinitionInterface $definition)
    {
        $this->name = $definition->name();
        $this->nodes = [];
        $this->edges = [];
        $this->initializers = [];
        $this->definition = $definition;
    }

    /**
     * @param string $id
     * @param string $component
     * @return $this
     */
    public function addNode($id, $component)
    {
        $node = [
            'id' => $id,
            'component' => $component,
        ];

        $this->nodes[$id] = $node;
        $this->emit('add.node', [$node]);

        return $this;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function removeNode($id)
    {
        foreach ($this->edges as $edge) {
            if ($edge['from']['node'] == $id) {
                $this->removeEdge($id, $edge['from']['port']);
            }
            if ($edge['to']['node'] == $id) {
                $this->removeEdge($id, $edge['to']['port']);
            }
        }

        foreach ($this->initializers as $initializer) {
            if ($initializer['to']['node'] == $id) {
                $this->removeEdge($id, $initializer['to']['port']);
            }
        }

        $node = $this->nodes[$id];
        $this->emit('remove.node', [$node]);
        unset($this->nodes[$id]);

        return $this;
    }

    /**
     * @param string $id
     * @return mixed|null
     */
    public function getNode($id)
    {
        if (!isset($this->nodes[$id])) {
            return null;
        }

        return $this->nodes[$id];
    }

    /**
     * @param string $outNode
     * @param string $outPort
     * @param string $inNode
     * @param string $inPort
     * @return $this
     */
    public function addEdge($outNode, $outPort, $inNode, $inPort)
    {
        $edge = [
            'from' => [
                'node' => $outNode,
                'port' => $outPort,
            ],
            'to' => [
                'node' => $inNode,
                'port' => $inPort,
            ],
        ];

        $this->edges[] = $edge;
        $this->emit('add.edge', [$edge]);

        return $this;
    }

    /**
     * @param string $node
     * @param string $port
     * @return $this
     */
    public function removeEdge($node, $port)
    {
        foreach ($this->edges as $index => $edge) {
            if ($edge['from']['node'] == $node && $edge['from']['port'] == $port) {
                $this->emit('remove.edge', [$edge]);
                $this->edges = array_splice($this->edges, $index, 1);
            }

            if ($edge['to']['node'] == $node && $edge['to']['port'] == $port) {
                $this->emit('remove.edge', [$edge]);
                $this->edges = array_splice($this->edges, $index, 1);
            }
        }

        foreach ($this->initializers as $index => $initializer) {
            if ($initializer['to']['node'] == $node && $initializer['to']['port'] == $port) {
                $this->emit('remove.edge', [$initializer]);
                $this->initializers = array_splice($this->initializers, $index, 1);
            }
        }

        return $this;
    }

    /**
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return $this
     */
    public function addInitial($data, $node, $port)
    {
        $initializer = [
            'from' => [
                'data' => $data,
            ],
            'to' => [
                'node' => $node,
                'port' => $port,
            ],
        ];

        $this->initializers[] = $initializer;
        $this->emit('add.edge', [$initializer]);

        return $this;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return $this->definition->toJson();
    }

    /**
     * Save the graph json into the file.
     *
     * @param string $file
     * @return bool
     */
    public function save($file)
    {
        $stat = file_put_contents($file, $this->definition->toFbp());

        if ($stat === false) {
            return false;
        }

        return true;
    }

    /**
     * Load PhpFlo graph definition from string.
     *
     * @param string $string FBP defnition string
     * @throws InvalidDefinitionException
     * @return Graph
     */
    public static function loadString($string)
    {
        $loader = new FbpParser($string);
        $definition = $loader->run();

        return self::loadDefinition($definition);
    }

    /**
     * Load PhpFlo graph definition from file.
     *
     * @param string $file
     * @throws InvalidDefinitionException
     * @return Graph
     */
    public static function loadFile($file)
    {
        return self::loadDefinition(
            Loader::load($file)
        );
    }

    /**
     * Load PhpFlo graph definition.
     *
     * @param DefinitionInterface $definition
     * @return \PhpFlo\Graph
     */
    public static function loadDefinition(DefinitionInterface $definition)
    {
        $graph = new Graph($definition);

        foreach ($definition->processes() as $id => $def) {
            $graph->addNode($id, $def['component']);
        }

        foreach ($definition->initializers() as $initializer) {
            $graph->addInitial(
                $initializer['data'],
                $initializer['tgt']['process'],
                $initializer['tgt']['port']
            );
        }

        foreach ($definition->connections() as $conn) {
            $graph->addEdge(
                $conn['src']['process'],
                $conn['src']['port'],
                $conn['tgt']['process'],
                $conn['tgt']['port']
            );
        }

        return $graph;
    }
}
