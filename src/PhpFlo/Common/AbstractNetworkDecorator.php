<?php
/*
 * This file is part of the phpflo/phpflo-flowtrace package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);
namespace PhpFlo\Common;

/**
 * Class AbstractNetworkDecorator
 *
 * @package PhpFlo\Common
 * @author Marc Aschmann <maschmann@gmail.com>
 */
abstract class AbstractNetworkDecorator implements NetworkInterface
{
    /**
     * @var NetworkInterface
     */
    protected $network;

    /**
     * AbstractNetworkAdapter constructor.
     *
     * @param NetworkInterface $network
     */
    public function __construct(NetworkInterface $network)
    {
        $this->network = $network;
    }

    /**
     * @return NetworkInterface
     */
    public function getNetwork() : NetworkInterface
    {
        return $this->network;
    }

    /**
     * @return null|Graph
     */
    public function getGraph()
    {
        return $this->network->getGraph();
    }

    /**
     * Cleanup network state after runs.
     *
     * @return NetworkInterface
     */
    public function shutdown() : NetworkInterface
    {
        $this->network->shutdown();

        return $this;
    }

    /**
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return NetworkInterface
     * @throws InvalidDefinitionException
     */
    public function addInitial($data, string $node, string $port) : NetworkInterface
    {
        $this->network->addInitial($data, $node, $port);

        return $this;
    }

    /**
     * Add a closure to an event
     *
     * Accepted events are connect, disconnect and data
     * Closures will be given the
     *
     * @param string $alias
     * @param string $event
     * @param \Closure $closure
     * @throws FlowException
     * @throws InvalidTypeException
     * @return $this
     */
    public function hook(string $alias, string $event, \Closure $closure)
    {
        $this->network->hook($alias, $event, $closure);

        return $this;
    }

    /**
     * Get all defined custom event hooks
     *
     * @return array
     */
    public function hooks() : array
    {
        return $this->network->hooks();
    }

    /**
     * @return bool|\DateInterval
     */
    public function uptime()
    {
        return $this->network->uptime();
    }

    /**
     * @param array $node
     * @return NetworkInterface
     * @throws \PhpFlo\Common\InvalidDefinitionException
     */
    public function addNode(array $node) : NetworkInterface
    {
        $this->network->addNode($node);

        return $this;
    }

    /**
     * @param array $node
     * @return NetworkInterface
     */
    public function removeNode(array $node) : NetworkInterface
    {
        $this->network->removeNode($node);

        return $this;
    }

    /**
     * @param string $id
     * @return mixed|null
     */
    public function getNode(string $id)
    {
        return $this->network->getNode($id);
    }

    /**
     * @param array $edge
     * @return NetworkInterface
     * @throws \PhpFlo\Common\InvalidDefinitionException
     */
    public function addEdge(array $edge) : NetworkInterface
    {
        $this->network->addEdge($edge);

        return $this;
    }

    /**
     * @param array $edge
     * @return NetworkInterface
     */
    public function removeEdge(array $edge) : NetworkInterface
    {
        $this->network->removeEdge($edge);

        return $this;
    }

    /**
     * Add a flow definition as Graph object or definition file/string
     * and initialize the network processes/connections
     *
     * @param mixed $graph
     * @return NetworkInterface
     * @throws \PhpFlo\Common\InvalidDefinitionException
     */
    public function boot($graph) : NetworkInterface
    {
        $this->network->boot($graph);

        return $this;
    }

    /**
     * Add initialization data
     *
     * @param mixed $data
     * @param string $node
     * @param string $port
     * @return NetworkInterface
     * @throws FlowException
     */
    public function run($data, string $node, string $port) : NetworkInterface
    {
        $this->network->run($data, $node, $port);

        return $this;
    }
}
