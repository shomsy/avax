<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Caching\System\Flows\AnalyzeCacheStrategy;

use Avax\Components\SystemDesign\Caching\System\PublicSurface\CacheStrategy;

final readonly class AnalyzeCacheStrategy
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(CacheStrategy $strategy): array
    {
        return [
            'strategy' => $strategy->value,
            'consistency' => match ($strategy) {
                CacheStrategy::CACHE_ASIDE => 'eventual',
                CacheStrategy::WRITE_THROUGH => 'strong',
                CacheStrategy::WRITE_BACK => 'eventual',
                CacheStrategy::LOOK_ASIDE => 'eventual',
            },
            'writeAmplification' => match ($strategy) {
                CacheStrategy::CACHE_ASIDE => 1,
                CacheStrategy::WRITE_THROUGH => 2,
                CacheStrategy::WRITE_BACK => 0.5,
                CacheStrategy::LOOK_ASIDE => 1,
            },
            'readAmplification' => match ($strategy) {
                CacheStrategy::CACHE_ASIDE => 1,
                CacheStrategy::WRITE_THROUGH => 1,
                CacheStrategy::WRITE_BACK => 1,
                CacheStrategy::LOOK_ASIDE => 2,
            },
            'stampedeRisk' => match ($strategy) {
                CacheStrategy::CACHE_ASIDE => 'high',
                CacheStrategy::WRITE_THROUGH => 'low',
                CacheStrategy::WRITE_BACK => 'medium',
                CacheStrategy::LOOK_ASIDE => 'high',
            },
        ];
    }
}
