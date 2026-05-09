<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\AssociativeStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\ConcurrentStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\DataStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\GraphStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\HeapStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\LinearStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\MapStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\MatrixStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\PersistentStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\ProbabilisticStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\RequiredDataShape;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\SetStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\SimulatedStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\TreeStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\ArrayStorage;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\AssociativeArrayStorage;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\BitStringStorage;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\RingBufferStorage;
use Avax\Components\DataStack\Data\System\Foundation\Failure\EmptyStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\IndexOutOfBounds;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidCapacity;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class StructureKernelTest extends TestCase
{
    public function test_family_promises_are_interfaces() : void
    {
        $promises = [
            DataStructure::class,
            LinearStructure::class,
            AssociativeStructure::class,
            MapStructure::class,
            SetStructure::class,
            TreeStructure::class,
            GraphStructure::class,
            HeapStructure::class,
            MatrixStructure::class,
            ProbabilisticStructure::class,
            PersistentStructure::class,
            ConcurrentStructure::class,
            SimulatedStructure::class,
        ];

        foreach ($promises as $promise) {
            self::assertTrue((new ReflectionClass($promise))->isInterface());
        }
    }

    public function test_required_data_shape_tracks_missing_fields() : void
    {
        $shape = new RequiredDataShape(requiredFields: ['id', 'email', '']);
        $shape->require(field: 'name');

        self::assertTrue($shape->has(field: 'id'));
        self::assertFalse($shape->has(field: ''));
        self::assertSame(['id', 'email', 'name'], $shape->requiredFields());
        self::assertSame(['email'], $shape->missingFields(data: ['id' => 1, 'name' => 'AvaX']));
    }

    public function test_array_storage_is_immutable_and_indexed() : void
    {
        $storage  = new ArrayStorage(items: ['a', 'b']);
        $appended = $storage->append(value: 'c');
        $replaced = $appended->replace(index: 1, value: 'B');

        self::assertSame(['a', 'b'], $storage->values());
        self::assertSame(['a', 'b', 'c'], $appended->values());
        self::assertSame(['a', 'B', 'c'], $replaced->values());
        self::assertSame('B', $replaced->read(index: 1));
        self::assertFalse($replaced->isEmpty());
        self::assertSame(3, $replaced->count());
    }

    public function test_array_storage_rejects_missing_index() : void
    {
        $this->expectException(IndexOutOfBounds::class);

        (new ArrayStorage(items: ['a']))->read(index: 5);
    }

    public function test_associative_array_storage_preserves_keys() : void
    {
        $storage = new AssociativeArrayStorage(items: ['name' => 'AvaX']);
        $written = $storage->write(key: 'version', value: 1);
        $removed = $written->remove(key: 'name');

        self::assertTrue($written->has(key: 'name'));
        self::assertSame('AvaX', $written->read(key: 'name'));
        self::assertSame(['name', 'version'], $written->keys());
        self::assertSame(['version' => 1], $removed->values());
        self::assertSame('fallback', $removed->read(key: 'missing', default: 'fallback'));
    }

    public function test_ring_buffer_storage_enforces_capacity_and_fifo_front() : void
    {
        $storage = new RingBufferStorage(capacity: 2);
        $filled  = $storage->enqueue(value: 'A')->enqueue(value: 'B');

        self::assertSame(2, $filled->capacity());
        self::assertTrue($filled->isFull());
        self::assertSame('A', $filled->front());
        self::assertSame(['B'], $filled->dequeue()->values());
    }

    public function test_ring_buffer_storage_rejects_invalid_capacity() : void
    {
        $this->expectException(InvalidCapacity::class);

        new RingBufferStorage(capacity: 0);
    }

    public function test_ring_buffer_storage_rejects_overflow() : void
    {
        $this->expectException(InvalidCapacity::class);

        (new RingBufferStorage(capacity: 1))->enqueue(value: 'A')->enqueue(value: 'B');
    }

    public function test_ring_buffer_storage_rejects_empty_front() : void
    {
        $this->expectException(EmptyStructure::class);

        (new RingBufferStorage(capacity: 1))->front();
    }

    public function test_bit_string_storage_sets_and_clears_bits() : void
    {
        $storage = BitStringStorage::withSize(size: 4);
        $updated = $storage->set(index: 2)->set(index: 3)->clear(index: 2);

        self::assertSame('0000', $storage->bits());
        self::assertSame('0001', $updated->bits());
        self::assertFalse($updated->isSet(index: 2));
        self::assertTrue($updated->isSet(index: 3));
        self::assertSame(4, $updated->count());
    }

    public function test_bit_string_storage_rejects_invalid_bits() : void
    {
        $this->expectException(InvalidCapacity::class);

        new BitStringStorage(bits: '0102');
    }

    public function test_bit_string_storage_rejects_out_of_range_index() : void
    {
        $this->expectException(IndexOutOfBounds::class);

        BitStringStorage::withSize(size: 1)->set(index: 3);
    }
}
