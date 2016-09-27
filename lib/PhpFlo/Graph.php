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
use PhpFlo\Exception\InvalidDefinitionException;

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
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->nodes = [];
        $this->edges = [];
        $this->initializers = [];
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
        $json = [
            'properties' => [
                'name' => $this->name,
            ],
            'processes' => [],
            'connections' => [],
        ];

        foreach ($this->nodes as $node) {
            $json['processes'][$node['id']] = [
                'component' => $node['component'],
            ];
        }

        foreach ($this->edges as $edge) {
            $json['connections'][] = [
                'src' => [
                    'process' => $edge['from']['node'],
                    'port' => $edge['from']['port'],
                ],
                'tgt' => [
                    'process' => $edge['to']['node'],
                    'port' => $edge['to']['port'],
                ],
            ];
        }

        foreach ($this->initializers as $initializer) {
            $json['connections'][] = [
                'data' => $initializer['from']['data'],
                'tgt' => [
                    'process' => $initializer['to']['node'],
                    'port' => $initializer['to']['port'],
                ],
            ];
        }

        return json_encode($json, JSON_PRETTY_PRINT);
    }

    /**
     * Save the graph json into the file.
     *
     * @param string $file
     * @return bool
     */
    public function save($file)
    {
        $stat = file_put_contents($file, $this->toJson());

        if ($stat === false) {
            return false;
        }

        return true;
    }

    /**
     * Load PhpFlo graph definition from string.
     *
     * @param string $string
     * @throws InvalidDefinitionException
     * @return Graph
     */
    public static function loadString($string)
    {
        $definition = @json_decode($string); // every time you @, god kills a kitten!

        if (!$definition) {
            throw new InvalidDefinitionException("Failed to parse PhpFlo graph definition string");
        }

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
        if (!file_exists($file)) {
            throw new InvalidDefinitionException("File {$file} not found");
        }

        $definition = @json_decode(file_get_contents($file));
        if (!$definition) {
            throw new InvalidDefinitionException("Failed to parse PhpFlo graph definition file {$file}");
        }

        return self::loadDefinition($definition);
    }

    /**
     * Load PhpFlo graph definition.
     *
     * @param \stdClass $definition
     * @return \PhpFlo\Graph
     */
    public static function loadDefinition($definition)
    {
        $graph = new Graph($definition->properties->name);

        foreach ($definition->processes as $id => $def) {
            $graph->addNode($id, $def->component);
        }

        foreach ($definition->connections as $conn) {
            if (isset($conn->data)) {
                $graph->addInitial($conn->data, $conn->tgt->process, $conn->tgt->port);
                continue;
            }

            $graph->addEdge($conn->src->process, $conn->src->port, $conn->tgt->process, $conn->tgt->port);
        }

        return $graph;
    }
}
