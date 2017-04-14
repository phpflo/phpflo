<?php
namespace Tests\PhpFlo;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PhpFlo\Common\ComponentBuilderInterface;
use PhpFlo\Common\ComponentInterface;
use PhpFlo\Common\PortInterface;
use PhpFlo\Core\Builder\ComponentFactory;
use PhpFlo\Common\NetworkInterface;
use PhpFlo\Core\Component;
use PhpFlo\Core\Graph;
use PhpFlo\Core\Interaction\PortRegistry;
use PhpFlo\Core\Network;
use PhpFlo\Core\Test\TestCase;

class NetworkTest extends TestCase
{
    /**
     * @var vfsStreamFile
     */
    private $filesystem;

    public function setUp()
    {
        $content = <<<EOF
ReadFile(ReadFile) out -> in SplitbyLines(SplitStr)
SplitbyLines(SplitStr) out -> in CountLines(Counter)
CountLines(Counter) count -> in Display(Output)
EOF;
        $root = vfsStream::setup();
        $this->filesystem = vfsStream::newFile('count.fbp')->at($root);
        $this->filesystem->setContent($content);
    }

    public function testLoadFile()
    {
        $builder = $this->stub(
            ComponentBuilderInterface::class,
            [
                'build' => \Closure::bind(
                    function ($name) {
                        $component = new Component();
                        switch ($name) {
                            case 'ReadFile':
                                $component->inPorts()->add('source', []);
                                $component->outPorts()->add('out', []);
                                $component->outPorts()->add('error', []);
                                break;
                            case 'SplitStr':
                                $component->inPorts()->add('in', []);
                                $component->outPorts()->add('out', []);
                                break;
                            case 'Counter':
                                $component->inPorts()->add('in', []);
                                $component->outPorts()->add('count', []);
                                break;
                            case 'Output':
                                $component->inPorts()->add('in', ['addressable' => true]);
                                break;
                        }

                        return $component;
                    },
                    $this
                ),
            ]
        );

        $network = new Network($builder);
        $network->boot($this->filesystem->url());
        $readFile = $network->getNode('ReadFile');
        $this->assertEquals('ReadFile', $readFile['id']);

        return $network;
    }

    /**
     * @param NetworkInterface $network
     * @depends testLoadFile
     */
    public function testStartupTime(NetworkInterface $network)
    {
        $this->assertInstanceOf(\DateInterval::class, $network->uptime());
    }

    /**
     * @param NetworkInterface $network
     * @depends testLoadFile
     */
    public function testNodes(NetworkInterface $network)
    {
        $network->removeNode(['id' => 'CountLines']);
        $this->assertNull($network->getNode('CountLines'));
        $network->addNode(['id' => 'CountLines']);
        $this->assertTrue(is_array($network->getNode('CountLines')));
        // Test adding the existing node again
        $this->assertInstanceOf(NetworkInterface::class, $network->addNode(['id' => 'CountLines']));
    }

    /**
     * @param NetworkInterface $network
     * @depends testLoadFile
     */
    public function testGetGraph(NetworkInterface $network)
    {
        $this->assertInstanceOf(Graph::class, $network->getGraph());
    }

    /**
     * @param NetworkInterface $network
     * @depends testLoadFile
     */
    public function testAddEdge(NetworkInterface $network)
    {
        $network->addEdge(
            [
                'from' => [
                    'node' => 'ReadFile',
                    'port' => 'error',
                ],
                'to' => [
                    'node' => 'Display',
                    'port' => 'in',
                ]
            ]
        );
    }

    /**
     * @param NetworkInterface $network
     * @depends testLoadFile
     */
    public function testAddEdgeWithSource(NetworkInterface $network)
    {
        $network->addEdge(
            [
                'from' => [
                    'data' => 'somefilename.txt',
                ],
                'to' => [
                    'node' => 'ReadFile',
                    'port' => 'source',
                ]
            ]
        );
    }

    /**
     * @param NetworkInterface $network
     * @depends testLoadFile
     * @expectedException \PhpFlo\Common\Exception\InvalidDefinitionException
     */
    public function testAddEdgeWithInvalidInitializerTarget(NetworkInterface $network)
    {
        $network->addEdge(
            [
                'from' => [
                    'data' => 'somefilename.txt',
                ],
                'to' => [
                    'node' => 'IDoNotExist',
                    'port' => 'source',
                ]
            ]
        );
    }

    /**
     * @param NetworkInterface $network
     * @depends testLoadFile
     * @expectedException \PhpFlo\Common\Exception\InvalidDefinitionException
     */
    public function testNoProcessForInportException(NetworkInterface $network)
    {
        $network->addEdge(
            [
                'from' => [
                    'node' => 'SomeNonExistentComponent',
                    'port' => 'error',
                ],
                'to' => [
                    'node' => 'Display',
                    'port' => 'in',
                ]
            ]
        );
    }


    /**
     * @param NetworkInterface $network
     * @depends testLoadFile
     * @expectedException \PhpFlo\Common\Exception\InvalidDefinitionException
     */
    public function testNoProcessForOutPortException(NetworkInterface $network)
    {
        $network->addEdge(
            [
                'from' => [
                    'node' => 'ReadFile',
                    'port' => 'error',
                ],
                'to' => [
                    'node' => 'SomeNonExistentComponent',
                    'port' => 'in',
                ]
            ]
        );
    }
}
