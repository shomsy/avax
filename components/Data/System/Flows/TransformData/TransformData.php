<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Flows\TransformData;

final class TransformData
{
    public function transform(array $data, callable $transformer): array
    {
        return array_map($transformer, $data);
    }
}