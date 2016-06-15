<?php
/**
 * @package PhpFlo
 */
namespace PhpFlo;

use Evenement\EventEmitter;

/**
 * Class Graph
 *
 * @package PhpFlo
 * @author Henri Bergius <henri.bergius@iki.fi>
 */
class Graph extends EventEmitter
{
    private $name = "";
    public $nodes = [];
    public $edges = [];
    public $initializers = [];

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function addNode($id, $component)
    {
        $node = [
            'id' => $id,
            'component' => $component,
        ];

        $this->nodes[$id] = $node;
        $this->emit('addNode', [$node]);
    }

    public function removeNode($id)
    {
        // @todo: analyze - this seems not to work due to missing second param on removeEdge
        foreach ($this->edges as $edge) {
            if ($edge['from']['node'] == $id) {
                $this->removeEdge($edge);
            }
            if ($edge['to']['node'] == $id) {
                $this->removeEdge($edge);
            }
        }

        foreach ($this->initializers as $initializer) {
            if ($initializer['to']['node'] == $id) {
                $this->removeEdge($initializer);
            }
        }

        $node = $this->nodes[$id];
        $this->emit('removeNode', [$node]);
        unset($this->nodes[$id]);
    }

    public function getNode($id)
    {
        if (!isset($this->nodes[$id])) {
            return null;
        }

        return $this->nodes[$id];
    }

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
        $this->emit('addEdge', [$edge]);
    }

    public function removeEdge($node, $port)
    {
        foreach ($this->edges as $index => $edge) {
            if ($edge['from']['node'] == $node && $edge['from']['port'] == $port) {
                $this->emit('removeEdge', [$edge]);
                $this->edges = array_splice($this->edges, $index, 1);
            }

            if ($edge['to']['node'] == $node && $edge['to']['port'] == $port) {
                $this->emit('removeEdge', [$edge]);
                $this->edges = array_splice($this->edges, $index, 1);
            }
        }

        foreach ($this->initializers as $index => $initializer) {
            if ($initializer['to']['node'] == $node && $initializer['to']['port'] == $port) {
                $this->emit('removeEdge', [$initializer]);
                $this->initializers = array_splice($this->initializers, $index, 1);
            }
        }
    }

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
        $this->emit('addEdge', [$initializer]);
    }

    public function toJSON()
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

        // TODO: JSON_PRETTY_PRINT support in PHP 5.4
        return json_encode($json);
    }

    /**
     * Save the graph json into the file.
     *
     * @param string $file
     * @return bool
     */
    public function save($file)
    {
        $stat = file_put_contents($file, $this->toJSON());

        if ($stat === false) {
            return false;
        }

        return true;
    }

    /**
     * Load PhpFlo graph definition from string.
     *
     * @param string $string
     * @throws \InvalidArgumentException
     * @return \PhpFlo\Graph
     */
    public static function loadString($string)
    {
        $definition = @json_decode($string);

        if (!$definition) {
            throw new \InvalidArgumentException("Failed to parse PhpFlo graph definition string");
        }

        return self::loadDefinition($definition);
    }

    /**
     * Load PhpFlo graph definition from file.
     *
     * @param string $file
     * @throws \InvalidArgumentException
     * @return \PhpFlo\Graph
     */
    public static function loadFile($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("File {$file} not found");
        }

        $definition = @json_decode(file_get_contents($file));
        if (!$definition) {
            throw new \InvalidArgumentException("Failed to parse PhpFlo graph definition file {$file}");
        }

        return self::loadDefinition($definition);
    }

    /**
     * Load PhpFlo graph definition.
     *
     * @param stdClass $definition
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
