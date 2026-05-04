<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesign\Projections;

use Avax\Components\SystemDesign\Projections\System\Flows\DesignProjection\DesignProjection;
use Avax\Components\SystemDesign\Projections\System\PublicSurface\ProjectionType;
use Avax\Tests\TestCase;

final class ProjectionTypeTest extends TestCase
{
    public function test_all_projection_types_exist(): void
    {
        $this->assertCount(4, ProjectionType::cases());
    }

    public function test_design_projection_analyzes_materialized_view(): void
    {
        $flow = new DesignProjection();
        $result = $flow(ProjectionType::MATERIALIZED_VIEW);

        $this->assertSame('eventual', $result['consistency']);
        $this->assertSame('on-write', $result['updateFrequency']);
    }
}
