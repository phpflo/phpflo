<?php
/*
 * This file is part of the phpflo/phpflo package.
 *
 * (c) Henri Bergius <henri.bergius@iki.fi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace PhpFlo\Core;

use Evenement\EventEmitter;
use PhpFlo\Common\DefinitionInterface;
use PhpFlo\Common\NetworkInterface as Net;
use PhpFlo\Common\Exception\InvalidDefinitionException;
use PhpFlo\Fbp\FbpParser;
use PhpFlo\Fbp\Loader\Loader;

/**
 * Analyzes and creates definitions from flow graph file.
 *
 * @package PhpFlo\Core
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
     * @return Graph
     */
    public function addNode(string $id, string $component): Graph
    {
        $node = [
            Net::NODE_ID => $id,
            Net::COMPONENT => $component,
        ];

        $this->nodes[$id] = $node;
        $this->emit(Net::EVENT_ADD, [$node]);

        return $this;
    }

    /**
     * @param string $id
     * @return Graph
     */
    public function removeNode(string $id): Graph
    {
        foreach ($this->edges as $edge) {
            if ($edge[Net::SOURCE][Net::NODE] == $id) {
                $this->removeEdge($id, $edge[Net::SOURCE][Net::PORT]);
            }
            if ($edge[Net::TARGET][Net::NODE] == $id) {
                $this->removeEdge($id, $edge[Net::TARGET][Net::PORT]);
            }
        }

        foreach ($this->initializers as $initializer) {
            if ($initializer[Net::TARGET][Net::NODE] == $id) {
                $this->removeEdge($id, $initializer[Net::TARGET][Net::PORT]);
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
    public function getNode(string $id)
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
     * @return Graph
     */
    public function addEdge(
        string $outNode,
        string $outPort,
        string $inNode,
        string $inPort
    ): Graph {
        $edge = [
            Net::SOURCE => [
                Net::NODE => $outNode,
                Net::PORT => $outPort,
            ],
            Net::TARGET => [
                Net::NODE => $inNode,
                Net::PORT => $inPort,
            ],
        ];

        $this->edges[] = $edge;
        $this->emit(Net::EVENT_ADD_EDGE, [$edge]);

        return $this;
    }

    /**
     * @param string $node
     * @param string $port
     * @return Graph
     */
    public function removeEdge(string $node, string $port): Graph
    {
        foreach ($this->edges as $index => $edge) {
            if ($edge[Net::SOURCE][Net::NODE] == $node
                && $edge[Net::SOURCE][Net::PORT] == $port
            ) {
                $this->emit(Net::EVENT_REMOVE_EDGE, [$edge]);
                $this->edges = array_splice($this->edges, $index, 1);
            }

            if ($edge[Net::TARGET][Net::NODE] == $node
                && $edge[Net::TARGET][Net::PORT] == $port
            ) {
                $this->emit(Net::EVENT_REMOVE_EDGE, [$edge]);
                $this->edges = array_splice($this->edges, $index, 1);
            }
        }

        foreach ($this->initializers as $index => $initializer) {
            if ($initializer[Net::TARGET][Net::NODE] == $node
                && $initializer[Net::TARGET][Net::PORT] == $port
            ) {
                $this->emit(Net::EVENT_REMOVE_EDGE, [$initializer]);
                $this->initializers = array_splice($this->initializers, $index, 1);
            }
        }

        return $this;
    }

    /**
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return Graph
     */
    public function addInitial($data, string $node, string $port): Graph
    {
        $initializer = [
            Net::SOURCE => [
                Net::DATA => $data,
            ],
            Net::TARGET => [
                Net::NODE => $node,
                Net::PORT => $port,
            ],
        ];

        $this->initializers[] = $initializer;
        $this->emit(Net::EVENT_ADD_EDGE, [$initializer]);

        return $this;
    }

    /**
     * @return string
     */
    public function toFbp(): string
    {
        return $this->definition->toFbp();
    }

    /**
     * @return string
     */
    public function toYaml(): string
    {
        return $this->definition->toYaml();
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return $this->definition->toJson();
    }

    /**
     * Save the graph json into the file.
     *
     * @param string $file
     * @return bool
     */
    public function save(string $file): bool
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
    public static function loadString(string $string): Graph
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
    public static function loadFile(string $file): Graph
    {
        return self::loadDefinition(
            Loader::load($file)
        );
    }

    /**
     * Load PhpFlo graph definition.
     *
     * @param DefinitionInterface $definition
     * @return Graph
     */
    public static function loadDefinition(DefinitionInterface $definition): Graph
    {
        $graph = new Graph($definition);

        foreach ($definition->processes() as $id => $def) {
            $graph->addNode($id, $def[Net::COMPONENT]);
        }

        foreach ($definition->initializers() as $initializer) {
            $graph->addInitial(
                $initializer[Net::DATA],
                $initializer[Net::CONNECTION_TARGET][Net::PROCESS],
                $initializer[Net::CONNECTION_TARGET][Net::PORT]
            );
        }

        foreach ($definition->connections() as $conn) {
            $graph->addEdge(
                $conn[Net::CONNECTION_SOURCE][Net::PROCESS],
                $conn[Net::CONNECTION_SOURCE][Net::PORT],
                $conn[Net::CONNECTION_TARGET][Net::PROCESS],
                $conn[Net::CONNECTION_TARGET][Net::PORT]
            );
        }

        return $graph;
    }
}
