<?php
namespace Tests\PhpFlo;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PhpFlo\Builder\ComponentFactory;
use PhpFlo\Common\NetworkInterface;
use PhpFlo\Graph;
use PhpFlo\Network;

class NetworkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var vfsStreamFile
     */
    private $filesystem;

    public function setUp()
    {
        $content = <<<EOF
ReadFile(ReadFile) out -> in SplitbyLines(SplitStr)
ReadFile(ReadFile) error -> in Display(Output)
SplitbyLines(SplitStr) out -> in CountLines(Counter)
CountLines(Counter) count -> in Display(Output)
EOF;
        $root = vfsStream::setup();
        $this->filesystem = vfsStream::newFile('count.fbp')->at($root);
        $this->filesystem->setContent($content);
    }

    public function testLoadFile()
    {
        $builder = new ComponentFactory();

        $network = new Network($builder);
        $network
            ->boot($this->filesystem->url());

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
    /*public function testEdeges(NetworkInterface $network)
    {
        $network->addEdge('Count Lines', 'count', 'Display', 'in');
        $network->removeEdge();
    }*/
}
