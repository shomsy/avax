<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Flows;

use Avax\Components\DataStack\Database\Flows\Batch\Batch;
use Avax\Components\DataStack\Database\Flows\LazySequence\LazySequence;
use Avax\Components\DataStack\Database\Flows\Pipeline\Pipeline;
use Avax\Components\DataStack\Database\Flows\Window\Window;
use Avax\Tests\TestCase;

final class FlowFamilyTest extends TestCase
{
    public function test_pipeline_processes_stages_in_order() : void
    {
        $pipeline = new Pipeline()
            ->pipe(callback: static fn (int $value) : int => $value + 1, name: 'plus-one')
            ->pipe(callback: static fn (int $value) : int => $value * 2, name: 'times-two');

        $this->assertSame(8, $pipeline->process(input: 3));
    }

    public function test_lazy_sequence_defers_map_until_iteration() : void
    {
        $sequence = LazySequence::from(items: [1, 2, 3])
            ->map(callback: static fn (int $value) : int => $value * 2)
            ->take(limit: 2);

        $this->assertSame([2, 4], $sequence->toArray());
    }

    public function test_batch_splits_ordered_input() : void
    {
        $batch = Batch::from(items: [1, 2, 3, 4, 5], size: 2);

        $this->assertSame([[1, 2], [3, 4], [5]], $batch->all());
    }

    public function test_window_builds_sliding_windows() : void
    {
        $window = Window::from(items: [1, 2, 3, 4], size: 2);

        $this->assertSame([[1, 2], [2, 3], [3, 4]], $window->all());
    }
}
