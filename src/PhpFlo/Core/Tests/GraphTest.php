<?php
namespace Tests\PhpFlo;

use org\bovigo\vfs\vfsStream;
use PhpFlo\Core\Graph;
use PhpFlo\Core\Test\TestCase;

class GraphTest extends TestCase
{
    public function testLoadFile()
    {
        $fbp = <<<EOF
ReadFile(ReadFile) out -> in SplitbyLines(SplitStr)
ReadFile(ReadFile) error -> in Display(Output)
SplitbyLines(SplitStr) out -> in CountLines(Counter)
CountLines(Counter) count -> in Display(Output)
EOF;

        $root = vfsStream::setup();
        $file = vfsStream::newFile('count.fbp')->at($root);
        $file->setContent($fbp);

        $graph = Graph::loadFile($file->url());
        $readFile = $graph->getNode('ReadFile');
        $this->assertEquals('ReadFile', $readFile['id']);

        $this->assertEquals(4, count($graph->nodes));

        $this->assertEquals(null, $graph->getNode('non_existing_node'));

    }
}
