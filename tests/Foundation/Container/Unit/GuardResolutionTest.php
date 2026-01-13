<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Unit;

use Avax\Container\Features\Core\DTO\ErrorDTO;
use Avax\Container\Features\Core\DTO\SuccessDTO;
use Avax\Container\Guard\Enforce\GuardResolution;
use Avax\Container\Guard\Enforce\StrictResolutionPolicy;
use Avax\Container\Guard\Rules\ContainerPolicy;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * PHPUnit test coverage for Container component behavior.
 *
 * @see docs_md/tests/Unit/GuardResolutionTest.md#quick-summary
 */
final class GuardResolutionTest extends TestCase
{
    public function test_strict_policy_blocks_unknown_classes(): void
    {
        $policy = new ContainerPolicy(strict: true);
        $guard = new GuardResolution(policy: new StrictResolutionPolicy(policy: $policy));

        $result = $guard->check(abstract: 'MissingClass');

        $this->assertInstanceOf(expected: ErrorDTO::class, actual: $result);
        $this->assertSame(expected: 'policy.blocked', actual: $result->code);
    }

    public function test_strict_policy_allows_existing_classes(): void
    {
        $policy = new ContainerPolicy(strict: true);
        $guard = new GuardResolution(policy: new StrictResolutionPolicy(policy: $policy));

        $result = $guard->check(abstract: stdClass::class);

        $this->assertInstanceOf(expected: SuccessDTO::class, actual: $result);
    }
}
