<?php

declare(strict_types=1);

namespace Avax\Tests\SystemDesign\Sharding;

use Avax\Components\SystemDesign\Sharding\System\Flows\AnalyzeSharding\AnalyzeSharding;
use Avax\Components\SystemDesign\Sharding\System\PublicSurface\ShardingKey;
use Avax\Tests\TestCase;

final class ShardingKeyTest extends TestCase
{
    public function test_sharding_key_defines_parameters(): void
    {
        $key = new ShardingKey(
            field: 'user_id',
            shardCount: 16,
        );

        $this->assertSame('user_id', $key->field);
        $this->assertSame(16, $key->shardCount);
    }

    public function test_analyze_sharding_calculates_distribution(): void
    {
        $key = new ShardingKey(field: 'tenant_id', shardCount: 8);
        $flow = new AnalyzeSharding();
        $result = $flow($key, 100000);

        $this->assertSame(8, $result['shardCount']);
        $this->assertSame(12500, $result['recordsPerShard']);
    }
}
