<?php
namespace Tests\PhpFlo;

use PhpFlo\Core\Graph;
use PhpFlo\Core\Test\TestCase;

class GraphTest extends TestCase
{
    public function testLoadFile()
    {
        $graph = Graph::loadFile(__DIR__ . '/../../examples/linecount/count.json');
        $readFile = $graph->getNode('ReadFile');
        $this->assertEquals('ReadFile', $readFile['id']);

        $this->assertEquals(4, count($graph->nodes));

        $this->assertEquals(null, $graph->getNode('non_existing_node'));

    }
}