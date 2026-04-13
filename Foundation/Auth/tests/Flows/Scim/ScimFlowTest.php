<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Scim;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\Scim\ScimAccountState;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Scim\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Flow\Scim\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Flow\Scim\ScimFailed;
use Avax\Auth\System\Flow\Scim\SyncGroups\SyncScimGroupsData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class ScimFlowTest extends TestCase
{
    public function testScimDirectoryProvisionSyncRotateAndDeleteLifecycle() : void
    {
        $auth = $this->buildAuth();

        $directory = $auth->registerScimDirectory(new RegisterScimDirectoryData(
            tenantSlug  : 'acme',
            name        : 'Acme Workforce',
            groupRoleMap: [
                'admins' => ['admin'],
                'users' => ['user'],
            ]
        ));

        $provisioned = $auth->provisionScimUser(new ProvisionScimUserData(
            directoryId    : $directory->directory->directoryId,
            directoryToken : $directory->plainTextToken,
            externalId     : 'ext-1',
            email          : 'worker@acme.test',
            username       : 'worker',
            groups         : ['admins'],
            state          : ScimAccountState::ACTIVE
        ));
        $idempotent = $auth->provisionScimUser(new ProvisionScimUserData(
            directoryId    : $directory->directory->directoryId,
            directoryToken : $directory->plainTextToken,
            externalId     : 'ext-1',
            email          : 'worker@acme.test',
            username       : 'worker',
            groups         : ['admins'],
            state          : ScimAccountState::ACTIVE
        ));
        $synced = $auth->syncScimGroups(new SyncScimGroupsData(
            directoryId    : $directory->directory->directoryId,
            directoryToken : $directory->plainTextToken,
            externalId     : 'ext-1',
            groups         : ['users'],
            state          : ScimAccountState::SUSPENDED
        ));
        $users = $auth->readScimUsers($directory->directory->directoryId);

        $this->assertTrue($provisioned->created);
        $this->assertFalse($provisioned->idempotent);
        $this->assertTrue($idempotent->idempotent);
        $this->assertTrue($synced->driftDetected);
        $this->assertSame(['user'], $synced->roles);
        $this->assertCount(1, $users);
        $this->assertSame(ScimAccountState::SUSPENDED, $users[0]->state);

        $rotated = $auth->rotateScimToken($directory->directory->directoryId);

        try {
            $auth->provisionScimUser(new ProvisionScimUserData(
                directoryId    : $directory->directory->directoryId,
                directoryToken : $directory->plainTextToken,
                externalId     : 'ext-2',
                email          : 'old-token@acme.test',
                username       : 'old-token',
                groups         : ['users']
            ));
            $this->fail('Expected old SCIM token to fail after rotation.');
        } catch (ScimFailed $exception) {
            $this->assertSame('Invalid SCIM directory token.', $exception->getMessage());
        }

        $auth->deleteScimUser(new DeleteScimUserData(
            directoryId    : $rotated->directory->directoryId,
            directoryToken : $rotated->plainTextToken,
            externalId     : 'ext-1'
        ));

        $this->assertSame([], $auth->readScimUsers($directory->directory->directoryId));
    }

    private function buildAuth() : Auth
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();

        return Auth::configuration()
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec('scim-auth-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore($refreshTokens)
            ->ready();
    }
}
