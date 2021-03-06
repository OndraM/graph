<?php

namespace Graphp\Graph\Tests;

use Graphp\Graph\Edge;
use Graphp\Graph\EdgeDirected;
use Graphp\Graph\Graph;
use Graphp\Graph\Vertex;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function assertGraphEquals(Graph $expected, Graph $actual)
    {
        $f = function(Graph $graph){
            $ret = get_class($graph);
            $ret .= PHP_EOL . 'attributes: ' . json_encode($graph->getAttributes());
            $ret .= PHP_EOL . 'vertices: ' . count($graph->getVertices());
            $ret .= PHP_EOL . 'edges: ' . count($graph->getEdges());

            return $ret;
        };

        // assert graph base parameters are equal
        $this->assertEquals($f($expected), $f($actual));

        // next, assert that all vertices in both graphs are the same
        // do not use assertVertexEquals() in order to not increase assertion counter
        foreach ($expected->getVertices() as $vertex) {
            if ($this->getVertexDump($vertex) !== $this->getVertexDump($vertex)) {
                $this->fail();
            }
        }

        // next, assert that all edges in both graphs are the same
        // assertEdgeEquals() does not work, as the order of the edges is unknown
        // therefor, build an array of edge dump and make sure each entry has a match

        $edgesExpected = array();
        foreach ($expected->getEdges() as $edge) {
            $edgesExpected []= $this->getEdgeDump($edge);
        }

        foreach ($actual->getEdges() as $edge) {
            $dump = $this->getEdgeDump($edge);

            $pos = array_search($dump, $edgesExpected, true);
            if ($pos === false) {
                $this->fail('given edge ' . $dump . ' not found');
            } else {
                unset($edgesExpected[$pos]);
            }
        }
    }

    protected function assertVertexEquals(Vertex $expected, Vertex $actual)
    {
        $this->assertEquals($this->getVertexDump($expected), $this->getVertexDump($actual));
    }

    protected function assertEdgeEquals(Edge $expected, Edge $actual)
    {
        $this->assertEquals($this->getEdgeDump($expected), $this->getEdgeDump($actual));
    }

    private function getVertexDump(Vertex $vertex)
    {
        $ret = get_class($vertex);
        $ret .= PHP_EOL . 'attributes: ' . json_encode($vertex->getAttributes());

        return $ret;
    }

    private function getEdgeDump(Edge $edge)
    {
        $vertices = $edge->getGraph()->getVertices();
        $ret = get_class($edge) . ' ';
        if ($edge instanceof EdgeDirected) {
            $ret .= array_search($edge->getVertexStart(), $vertices) . ' -> ' . array_search($edge->getVertexEnd(), $vertices);
        } else {
            $foo = $edge->getVertices();
            $ret .= array_search($foo[0], $vertices) . ' -- ' . array_search($foo[1], $vertices);
        }
        $ret .= PHP_EOL . 'attributes: ' . json_encode($edge->getAttributes());

        return $ret;
    }

    public function setExpectedException($exception, $exceptionMessage = '', $exceptionCode = null)
    {
        if (method_exists($this, 'expectException')) {
            // PHPUnit 6+
            $this->expectException($exception);
            if ($exceptionMessage !== '') {
                $this->expectExceptionMessage($exceptionMessage);
            }
            if ($exceptionCode !== null) {
                $this->expectExceptionCode($exceptionCode);
            }
        } else {
            // legacy PHPUnit 4 - PHPUnit 5
            parent::setExpectedException($exception, $exceptionMessage, $exceptionCode);
        }
    }
}
