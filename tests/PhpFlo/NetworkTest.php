<?php
namespace Tests\PhpFlo;

use PhpFlo\Common\ComponentBuilderInterface;
use PhpFlo\Common\DefinitionInterface;
use PhpFlo\Component\Counter;
use PhpFlo\Component\Output;
use PhpFlo\Component\ReadFile;
use PhpFlo\Component\SplitStr;
use PhpFlo\Exception\InvalidTypeException;
use PhpFlo\Graph;
use PhpFlo\Network;

class NetworkTest extends \PHPUnit_Framework_TestCase
{
    const JSON_GRAPH_FILEPATH = __DIR__.'/../../examples/linecount/count.json';
    const FBP_GRAPH_FILEPATH = __DIR__.'/../../examples/linecount/count.fbp';

    /**
     * @var ComponentBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    /**
     * @var Network
     */
    private $network;

    public function setUp()
    {
        $this->builder = $this->createMock(ComponentBuilderInterface::class);
        $this->network = new Network($this->builder);
    }

    public function testBuildGraphFromFile()
    {
        $this->assertInstanceOf(
            Graph::class,
            $this->network->buildGraph(GraphTest::JSON_GRAPH_FILEPATH)
        );
    }

    public function testBuildGraphFromGraphObject()
    {
        $graph = new Graph($this->createMock(DefinitionInterface::class));

        $this->assertSame(
            $graph,
            $this->network->buildGraph($graph)
        );
    }

    public function testBuildGraphFromString()
    {
        $this->assertInstanceOf(
            Graph::class,
            $this->network->buildGraph(file_get_contents(GraphTest::FBP_GRAPH_FILEPATH))
        );
    }

    public function testBuildGraphFromInvalidObject()
    {
        $this->expectException(InvalidTypeException::class);
        $this->network->buildGraph(new \stdClass());

    }

    public function testBoot()
    {
        $this->builder
            ->expects($this->exactly(4))
            ->method('build')
            ->willReturnCallback(function($componentId) {
               switch ($componentId) {
                   case 'ReadFile':
                       return new ReadFile();
                   case 'SplitStr':
                       return new SplitStr();
                   case 'Counter':
                       return new Counter();
                   case 'Output':
                       return new Output();
               }
            });

        $this->assertSame(
            $this->network,
            $this->network->boot(GraphTest::JSON_GRAPH_FILEPATH)
        );

        $readFile = $this->network->getNode('ReadFile');
        $this->assertEquals('ReadFile', $readFile['id']);
    }
}
