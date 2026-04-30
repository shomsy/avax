<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\GoldenPath;

use Avax\Components\Fallback\System\PublicSurface\Fallback;
use Avax\Components\Pipeline\System\PublicSurface\Pipeline;
use Avax\Tests\TestCase;
use RuntimeException;

/**
 * GOLDEN PATH INTEGRATION TESTS
 */
class GoldenPathTest extends TestCase
{
    /**
     * @test
     */
    public function pipeline_hooks() : void
    {
        Pipeline::beforeController(static fn ($r) => $r);
        Pipeline::afterController(static fn ($r) => $r);

        $hooks = Pipeline::hooks();

        $this->assertIsArray($hooks);
    }

    /**
     * @test
     */
    public function fallback_chain() : void
    {
        $callCount = 0;

        $result = Fallback::execute(
            fallbacks: [
                           static function () use (&$callCount) {
                               $callCount++;
                               throw new RuntimeException('Primary failed');
                           },
                           static function () use (&$callCount) {
                               $callCount++;

                               return 'fallback_result';
                           },
                       ]
        );

        $this->assertEquals('fallback_result', $result);
        $this->assertEquals(2, $callCount);
    }
}