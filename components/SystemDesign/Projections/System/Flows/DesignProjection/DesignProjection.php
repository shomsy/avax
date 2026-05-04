<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Projections\System\Flows\DesignProjection;

use Avax\Components\SystemDesign\Projections\System\PublicSurface\ProjectionType;

final readonly class DesignProjection
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ProjectionType $type): array
    {
        return [
            'type' => $type->value,
            'consistency' => match ($type) {
                ProjectionType::READ_MODEL => 'eventual',
                ProjectionType::MATERIALIZED_VIEW => 'eventual',
                ProjectionType::AGGREGATE => 'strong',
                ProjectionType::COUNTER => 'strong',
            },
            'updateFrequency' => match ($type) {
                ProjectionType::READ_MODEL => 'on-read',
                ProjectionType::MATERIALIZED_VIEW => 'on-write',
                ProjectionType::AGGREGATE => 'on-write',
                ProjectionType::COUNTER => 'on-write',
            },
            'storageComplexity' => match ($type) {
                ProjectionType::READ_MODEL => 'low',
                ProjectionType::MATERIALIZED_VIEW => 'medium',
                ProjectionType::AGGREGATE => 'medium',
                ProjectionType::COUNTER => 'low',
            },
        ];
    }
}
