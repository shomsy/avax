<?php

declare(strict_types=1);
namespace Avax\Tests\Container\Unit;

use Avax\Container\Features\Core\DTO\ErrorDTO;
use Avax\Container\Features\Core\DTO\SuccessDTO;
use Avax\Container\Guard\Enforce\GuardResolution;
use Avax\Container\Guard\Enforce\StrictResolutionPolicy;
use Avax\Container\Guard\Rules\ContainerPolicy;
use PHPUnit\Framework\TestCase;
use stdClass;

final class GuardResolutionTest extends TestCase
{
    public function testStrictPolicyBlocksUnknownClasses() : void
    {
        $policy = new ContainerPolicy(strict: true);
        $guard  = new GuardResolution(policy: new StrictResolutionPolicy(policy: $policy));

        $result = $guard->check(abstract: 'Missing\\Class');

        $this->assertInstanceOf(expected: ErrorDTO::class, actual: $result);
        $this->assertSame(expected: 'policy.blocked', actual: $result->code);
    }

    public function testStrictPolicyAllowsExistingClasses() : void
    {
        $policy = new ContainerPolicy(strict: true);
        $guard  = new GuardResolution(policy: new StrictResolutionPolicy(policy: $policy));

        $result = $guard->check(abstract: stdClass::class);

        $this->assertInstanceOf(expected: SuccessDTO::class, actual: $result);
    }
}