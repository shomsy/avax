<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Persistence;

use Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\BloomFilter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class BloomFilterTest extends TestCase
{
    // ==================== Adding items to filter ====================

    public function test_add_single_item(): void
    {
        $filter = BloomFilter::create(100);
        $filter->add('hello');

        $this->assertSame(1, $filter->getItemCount());
    }

    public function test_add_multiple_items(): void
    {
        $filter = BloomFilter::create(100);
        $filter->add('hello');
        $filter->add('world');
        $filter->add('foo');

        $this->assertSame(3, $filter->getItemCount());
    }

    public function test_add_duplicate_items(): void
    {
        $filter = BloomFilter::create(100);
        $filter->add('hello');
        $filter->add('hello');
        $filter->add('hello');

        $this->assertSame(3, $filter->getItemCount());
    }

    public function test_add_empty_string(): void
    {
        $filter = BloomFilter::create(100);
        $filter->add('');

        $this->assertSame(1, $filter->getItemCount());
    }

    // ==================== mightContain() returning true for added items ====================

    public function test_might_contain_returns_true_for_added_item(): void
    {
        $filter = BloomFilter::create(1000);
        $filter->add('hello');

        $this->assertTrue($filter->mightContain('hello'));
    }

    public function test_might_contain_returns_true_for_all_added_items(): void
    {
        $filter = BloomFilter::create(1000);
        $items  = ['apple', 'banana', 'cherry', 'date', 'elderberry'];

        foreach ($items as $item) {
            $filter->add($item);
        }

        foreach ($items as $item) {
            $this->assertTrue($filter->mightContain($item), "Filter should contain: {$item}");
        }
    }

    // ==================== mightContain() possibly returning false for non-added items ====================

    public function test_might_contain_returns_false_for_definitely_not_present(): void
    {
        $filter = BloomFilter::create(1000, 0.001);
        $filter->add('hello');

        $this->assertFalse($filter->mightContain('world'));
    }

    public function test_might_contain_returns_false_for_many_non_added_items(): void
    {
        $filter = BloomFilter::create(1000, 0.001);

        for ($i = 0; $i < 10; $i++) {
            $filter->add("item_{$i}");
        }

        // These were never added
        $this->assertFalse($filter->mightContain('never_added'));
        $this->assertFalse($filter->mightContain('not_present'));
    }

    public function test_definitely_not_contains(): void
    {
        $filter = BloomFilter::create(1000, 0.001);
        $filter->add('hello');

        $this->assertTrue($filter->definitelyNotContains('world'));
        $this->assertFalse($filter->definitelyNotContains('hello'));
    }

    // ==================== Optimal size calculation ====================

    public function test_create_calculates_optimal_size(): void
    {
        $filter = BloomFilter::create(100, 0.01);

        $this->assertGreaterThan(0, $filter->getBitCount());
        $this->assertGreaterThan(0, $filter->getHashCount());
    }

    public function test_create_with_more_items_larger_filter(): void
    {
        $filter1 = BloomFilter::create(100, 0.01);
        $filter2 = BloomFilter::create(1000, 0.01);

        $this->assertGreaterThan($filter1->getBitCount(), $filter2->getBitCount());
    }

    public function test_create_with_lower_fpr_larger_filter(): void
    {
        $filter1 = BloomFilter::create(100, 0.1);
        $filter2 = BloomFilter::create(100, 0.001);

        $this->assertGreaterThan($filter1->getBitCount(), $filter2->getBitCount());
    }

    public function test_create_with_expected_items(): void
    {
        $filter = BloomFilter::create(500, 0.05);

        $this->assertSame(500, $filter->getExpectedItems());
    }

    public function test_create_with_expected_fpr(): void
    {
        $filter = BloomFilter::create(100, 0.03);

        $this->assertSame(0.03, $filter->getFalsePositiveRate());
    }

    // ==================== Double hashing (CRC32-based) ====================

    public function test_same_item_always_produces_same_bits(): void
    {
        $filter1 = BloomFilter::withSize(100, 3);
        $filter2 = BloomFilter::withSize(100, 3);

        $filter1->add('test_item');
        $filter2->add('test_item');

        $this->assertSame($filter1->getBits(), $filter2->getBits());
    }

    public function test_different_items_produce_different_bits(): void
    {
        $filter1 = BloomFilter::withSize(1000, 4);
        $filter2 = BloomFilter::withSize(1000, 4);

        $filter1->add('item_a');
        $filter2->add('item_b');

        $this->assertNotSame($filter1->getBits(), $filter2->getBits());
    }

    // ==================== Merge two filters ====================

    public function test_merge_two_filters(): void
    {
        $filter1 = BloomFilter::withSize(1000, 3);
        $filter2 = BloomFilter::withSize(1000, 3);

        $filter1->add('hello');
        $filter2->add('world');

        $filter1->merge($filter2);

        $this->assertTrue($filter1->mightContain('hello'));
        $this->assertTrue($filter1->mightContain('world'));
    }

    public function test_merge_preserves_items_from_both_filters(): void
    {
        $filter1 = BloomFilter::withSize(1000, 3);
        $filter2 = BloomFilter::withSize(1000, 3);

        foreach (['a', 'b', 'c'] as $item) {
            $filter1->add($item);
        }
        foreach (['d', 'e', 'f'] as $item) {
            $filter2->add($item);
        }

        $filter1->merge($filter2);

        foreach (['a', 'b', 'c', 'd', 'e', 'f'] as $item) {
            $this->assertTrue($filter1->mightContain($item));
        }
    }

    public function test_merge_increments_item_count(): void
    {
        $filter1 = BloomFilter::withSize(1000, 3);
        $filter2 = BloomFilter::withSize(1000, 3);

        $filter1->add('a');
        $filter1->add('b');
        $filter2->add('c');

        $filter1->merge($filter2);

        $this->assertSame(3, $filter1->getItemCount());
    }

    public function test_merge_incompatible_bit_counts_throws(): void
    {
        $filter1 = BloomFilter::withSize(100, 3);
        $filter2 = BloomFilter::withSize(200, 3);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot merge bloom filters with different bit counts');

        $filter1->merge($filter2);
    }

    public function test_merge_incompatible_hash_counts_throws(): void
    {
        $filter1 = BloomFilter::withSize(100, 3);
        $filter2 = BloomFilter::withSize(100, 5);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot merge bloom filters with different hash counts');

        $filter1->merge($filter2);
    }

    // ==================== Saturation detection ====================

    public function test_saturation_not_saturated_when_empty(): void
    {
        $filter = BloomFilter::withSize(100, 3);

        $this->assertFalse($filter->isSaturated());
    }

    public function test_saturation_not_saturated_when_few_items(): void
    {
        $filter = BloomFilter::withSize(1000, 3);

        for ($i = 0; $i < 10; $i++) {
            $filter->add("item_{$i}");
        }

        $this->assertFalse($filter->isSaturated());
    }

    public function test_saturation_detected_when_many_items(): void
    {
        // Small filter to quickly saturate
        $filter = BloomFilter::withSize(20, 2);

        for ($i = 0; $i < 50; $i++) {
            $filter->add("item_{$i}");
        }

        $this->assertTrue($filter->isSaturated());
    }

    public function test_fill_ratio_increases_with_items(): void
    {
        $filter = BloomFilter::withSize(100, 3);

        $ratioBefore = $filter->fillRatio();
        $filter->add('hello');
        $ratioAfter = $filter->fillRatio();

        $this->assertGreaterThanOrEqual($ratioBefore, $ratioAfter);
    }

    public function test_fill_ratio_is_zero_when_empty(): void
    {
        $filter = BloomFilter::withSize(100, 3);

        $this->assertSame(0.0, $filter->fillRatio());
    }

    // ==================== False positive rate estimation ====================

    public function test_estimated_false_positive_rate_zero_when_empty(): void
    {
        $filter = BloomFilter::create(100);

        $this->assertSame(0.0, $filter->estimatedFalsePositiveRate());
    }

    public function test_estimated_false_positive_rate_increases_with_items(): void
    {
        $filter = BloomFilter::withSize(1000, 3);

        $rate1 = $filter->estimatedFalsePositiveRate();
        $filter->add('item1');
        $rate2 = $filter->estimatedFalsePositiveRate();
        $filter->add('item2');
        $rate3 = $filter->estimatedFalsePositiveRate();

        $this->assertGreaterThanOrEqual($rate1, $rate2);
        $this->assertGreaterThanOrEqual($rate2, $rate3);
    }

    public function test_estimated_fpr_is_between_zero_and_one(): void
    {
        $filter = BloomFilter::create(100, 0.01);

        for ($i = 0; $i < 50; $i++) {
            $filter->add("item_{$i}");
        }

        $fpr = $filter->estimatedFalsePositiveRate();

        $this->assertGreaterThanOrEqual(0.0, $fpr);
        $this->assertLessThanOrEqual(1.0, $fpr);
    }

    // ==================== Factory methods with different FPR values ====================

    public function test_create_with_default_fpr(): void
    {
        $filter = BloomFilter::create(100);

        $this->assertSame(0.01, $filter->getFalsePositiveRate());
    }

    public function test_create_with_custom_fpr(): void
    {
        $filter = BloomFilter::create(100, 0.05);

        $this->assertSame(0.05, $filter->getFalsePositiveRate());
    }

    public function test_create_with_very_low_fpr(): void
    {
        $filter = BloomFilter::create(100, 0.001);

        $this->assertSame(0.001, $filter->getFalsePositiveRate());
    }

    public function test_with_size_factory(): void
    {
        $filter = BloomFilter::withSize(500, 5);

        $this->assertSame(500, $filter->getBitCount());
        $this->assertSame(5, $filter->getHashCount());
    }

    public function test_with_size_invalid_bit_count_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bit count must be greater than zero');

        BloomFilter::withSize(0, 3);
    }

    public function test_with_size_invalid_hash_count_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hash count must be greater than zero');

        BloomFilter::withSize(100, 0);
    }

    public function test_create_invalid_expected_items_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected items must be greater than zero');

        BloomFilter::create(0);
    }

    public function test_create_negative_expected_items_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BloomFilter::create(-1);
    }

    public function test_create_invalid_fpr_zero_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('False positive rate must be between 0 and 1');

        BloomFilter::create(100, 0.0);
    }

    public function test_create_invalid_fpr_one_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BloomFilter::create(100, 1.0);
    }

    public function test_create_invalid_fpr_negative_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BloomFilter::create(100, -0.1);
    }

    // ==================== Reset ====================

    public function test_reset_clears_filter(): void
    {
        $filter = BloomFilter::withSize(1000, 3);
        $filter->add('hello');
        $filter->add('world');

        $filter->reset();

        $this->assertSame(0, $filter->getItemCount());
        $this->assertSame(0.0, $filter->fillRatio());
    }

    public function test_reset_then_reuse(): void
    {
        $filter = BloomFilter::withSize(1000, 3);
        $filter->add('old_item');
        $filter->reset();
        $filter->add('new_item');

        $this->assertFalse($filter->mightContain('old_item'));
        $this->assertTrue($filter->mightContain('new_item'));
    }

    // ==================== Accessor methods ====================

    public function test_get_bit_count(): void
    {
        $filter = BloomFilter::create(100);

        $this->assertGreaterThan(0, $filter->getBitCount());
    }

    public function test_get_hash_count(): void
    {
        $filter = BloomFilter::create(100);

        $this->assertGreaterThan(0, $filter->getHashCount());
    }

    public function test_get_bits(): void
    {
        $filter = BloomFilter::withSize(100, 3);

        $bits = $filter->getBits();

        $this->assertIsArray($bits);
        $this->assertCount(100, $bits);
    }

    public function test_set_bit_count(): void
    {
        $filter = BloomFilter::withSize(100, 3);

        $this->assertSame(0, $filter->setBitCount());
        $filter->add('hello');
        $this->assertGreaterThan(0, $filter->setBitCount());
    }

    // ==================== Large scale test ====================

    public function test_large_filter_accuracy(): void
    {
        $filter = BloomFilter::create(10000, 0.01);

        // Add 10000 items
        for ($i = 0; $i < 10000; $i++) {
            $filter->add("item_{$i}");
        }

        // All added items should be found
        $falseNegatives = 0;
        for ($i = 0; $i < 10000; $i++) {
            if (! $filter->mightContain("item_{$i}")) {
                $falseNegatives++;
            }
        }

        $this->assertSame(0, $falseNegatives, 'Bloom filter should never have false negatives');
    }
}
