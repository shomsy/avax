<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Capabilities\Policies;

use Avax\Container\DependencyInjection\Capabilities\Policies\CheckResolutionPolicy;
use Avax\Container\DependencyInjection\Capabilities\Policies\ContainerPolicy;
use Avax\Container\DependencyInjection\Capabilities\Policies\Decisions\ResolutionAllowed;
use Avax\Container\DependencyInjection\Capabilities\Policies\Decisions\ResolutionBlocked;
use Avax\Container\DependencyInjection\Capabilities\Policies\StrictResolutionPolicy;
use PHPUnit\Framework\TestCase;
use stdClass;

final class CheckResolutionPolicyTest extends TestCase
{
    public function test_strict_policy_blocks_unknown_classes() : void
    {
        $check = new CheckResolutionPolicy(
            policy: new StrictResolutionPolicy(policy: new ContainerPolicy(strict: true))
        );

        $result = $check->check(abstract: 'MissingClass');

        $this->assertInstanceOf(expected: ResolutionBlocked::class, actual: $result);
        $this->assertSame(expected: 'policy.blocked', actual: $result->code);
    }

    public function test_strict_policy_allows_existing_classes() : void
    {
        $check = new CheckResolutionPolicy(
            policy: new StrictResolutionPolicy(policy: new ContainerPolicy(strict: true))
        );

        $result = $check->check(abstract: stdClass::class);

        $this->assertInstanceOf(expected: ResolutionAllowed::class, actual: $result);
    }
}
