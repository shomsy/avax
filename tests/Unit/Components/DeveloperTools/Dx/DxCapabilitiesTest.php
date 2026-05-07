<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DeveloperTools\Dx;

use Avax\Components\DeveloperTools\Dx\System\System\Capabilities\Capabilities\Graph\DependencyGraph;
use Avax\Components\DeveloperTools\Dx\System\System\Capabilities\Capabilities\Graph\DependencyNode;
use PHPUnit\Framework\TestCase;

final class DxCapabilitiesTest extends TestCase
{
    public function test_dependency_graph_adds_nodes() : void
    {
        $graph = new DependencyGraph();
        $graph->add('A', ['B']);

        $this->assertSame(['B'], $graph->dependsOn('A'));
        $this->assertSame(['A'], $graph->dependents('B'));
    }
}
