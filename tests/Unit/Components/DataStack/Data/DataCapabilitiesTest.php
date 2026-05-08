<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection;
use PHPUnit\Framework\TestCase;

final class DataCapabilitiesTest extends TestCase
{
    public function test_collection_basic_operations() : void
    {
        $collection = Collection::make([1, 2, 3, 4, 5]);

        $doubled = $collection->map(fn ($n) => $n * 2);
        $this->assertSame([2, 4, 6, 8, 10], $doubled->all());

        $filtered = $collection->filter(fn ($n) => $n > 3);
        $this->assertSame([3 => 4, 4 => 5], $filtered->all()); // array_filter preserves keys

        $this->assertSame(1, $collection->first());
    }
}
