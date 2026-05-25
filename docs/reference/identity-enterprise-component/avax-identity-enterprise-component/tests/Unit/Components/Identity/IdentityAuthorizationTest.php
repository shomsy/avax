<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity;

use Avax\Components\Identity\Capabilities\Permissions\PermissionGrant;
use Avax\Components\Identity\Configuration\CreateIdentityRuntimeGraph;
use Avax\Components\Identity\Configuration\IdentityConfiguration;
use Avax\Components\Identity\Flows\AuthorizeAction\AuthorizationRequest;
use Avax\Components\Identity\Foundation\Values\Permission;
use Avax\Components\Identity\Foundation\Values\TokenSecret;
use Avax\Components\Identity\Foundation\Values\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdentityAuthorizationTest extends TestCase
{
    #[Test]
    public function authorization_accepts_explicit_grant(): void
    {
        $graph = (new CreateIdentityRuntimeGraph())->create(new IdentityConfiguration(TokenSecret::fromString(str_repeat('c', 32))));
        $userId = UserId::fromString('user-1');
        $permission = Permission::for('read', 'invoice');
        $decision = $this->readGraphProperty($graph->authorization()->authorizeAction(), 'permissions');
        $directory = $this->readGraphProperty($decision, 'permissions');
        $directory->grant(new PermissionGrant($userId, $permission));

        $result = $graph->authorization()->authorizeAction()->authorize(new AuthorizationRequest($userId, $permission));

        self::assertTrue($result->isAllowed());
    }

    private function readGraphProperty(object $object, string $property): object
    {
        $reflection = new \ReflectionProperty($object, $property);

        return $reflection->getValue($object);
    }
}
