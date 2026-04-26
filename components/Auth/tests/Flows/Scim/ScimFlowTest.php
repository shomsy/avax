<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Scim;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\Access\Authentication\Throttle\AttemptThrottle;
use Avax\Auth\System\Capabilities\Access\Authentication\Throttle\InMemoryAttemptThrottleStore;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Codec\HmacTokenCodec;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryTokenRevocationStore;
use Avax\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkOperation;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\Bulk\ScimBulkRequest;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\DeleteUser\DeleteScimUserData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\MarkOutage\MarkScimDirectoryOutageData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ProvisionUser\ProvisionScimUserData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ScimFailed;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\SyncGroups\SyncScimGroupsData;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimAccountState;
use Avax\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class ScimFlowTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function testScimDirectoryProvisionSyncRotateAndDeleteLifecycle() : void
    {
        $auth = $this->buildAuth();

        $directory = $auth->registerScimDirectory(data: new RegisterScimDirectoryData(
                                                            tenantSlug  : 'acme',
                                                            name        : 'Acme Workforce',
                                                            groupRoleMap: [
                                                                              'admins' => ['admin'],
                                                                              'users'  => ['user'],
                                                                          ]
                                                        ));

        $provisioned = $auth->provisionScimUser(data: new ProvisionScimUserData(
                                                          directoryId   : $directory->directory->directoryId,
                                                          directoryToken: $directory->plainTextToken,
                                                          externalId    : 'ext-1',
                                                          email         : 'worker@acme.test',
                                                          username      : 'worker',
                                                          groups        : ['admins'],
                                                          state         : ScimAccountState::ACTIVE
                                                      ));
        $idempotent  = $auth->provisionScimUser(data: new ProvisionScimUserData(
                                                          directoryId   : $directory->directory->directoryId,
                                                          directoryToken: $directory->plainTextToken,
                                                          externalId    : 'ext-1',
                                                          email         : 'worker@acme.test',
                                                          username      : 'worker',
                                                          groups        : ['admins'],
                                                          state         : ScimAccountState::ACTIVE
                                                      ));
        $synced      = $auth->syncScimGroups(data: new SyncScimGroupsData(
                                                       directoryId   : $directory->directory->directoryId,
                                                       directoryToken: $directory->plainTextToken,
                                                       externalId    : 'ext-1',
                                                       groups        : ['users'],
                                                       state         : ScimAccountState::SUSPENDED
                                                   ));
        $users       = $auth->readScimUsers(directoryId: $directory->directory->directoryId);
        $groups      = $auth->readScimGroups(directoryId: $directory->directory->directoryId);

        $this->assertTrue(condition: $provisioned->created);
        $this->assertFalse(condition: $provisioned->idempotent);
        $this->assertTrue(condition: $idempotent->idempotent);
        $this->assertTrue(condition: $synced->driftDetected);
        $this->assertSame(expected: ['user'], actual: $synced->roles);
        $this->assertCount(expectedCount: 1, haystack: $users);
        $this->assertSame(expected: ScimAccountState::SUSPENDED, actual: $users[0]->state);
        $this->assertCount(expectedCount: 1, haystack: $groups);
        $this->assertSame(expected: 'users', actual: $groups[0]->groupId);
        $this->assertCount(expectedCount: 1, haystack: $groups[0]->members);

        $rotated = $auth->rotateScimToken(directoryId: $directory->directory->directoryId);

        try {
            $auth->provisionScimUser(data: new ProvisionScimUserData(
                                               directoryId   : $directory->directory->directoryId,
                                               directoryToken: $directory->plainTextToken,
                                               externalId    : 'ext-2',
                                               email         : 'old-token@acme.test',
                                               username      : 'old-token',
                                               groups        : ['users']
                                           ));
            $this->fail(message: 'Expected old SCIM token to fail after rotation.');
        } catch (ScimFailed $exception) {
            $this->assertSame(expected: 'Invalid SCIM directory token.', actual: $exception->getMessage());
        }

        $auth->deleteScimUser(data: new DeleteScimUserData(
                                        directoryId   : $rotated->directory->directoryId,
                                        directoryToken: $rotated->plainTextToken,
                                        externalId    : 'ext-1'
                                    ));

        $this->assertSame(expected: [], actual: $auth->readScimUsers(directoryId: $directory->directory->directoryId));
    }

    private function buildAuth(AttemptThrottle|null $scimThrottle = null) : Auth
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();

        $builder = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                                                                   userSource       : $userSource,
                                                                   codec            : new HmacTokenCodec(secret: 'scim-auth-secret'),
                                                                   clock            : new Clock(),
                                                                   revocationStore  : new InMemoryTokenRevocationStore(),
                                                                   refreshTokenStore: $refreshTokens
                                                               )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens);

        if ($scimThrottle !== null) {
            $builder = $builder->withScimThrottle(scimThrottle: $scimThrottle);
        }

        return $builder->ready();
    }

    /**
     * @throws RandomException
     */
    public function testScimBulkCanCreateReplaceAndDeleteUsers() : void
    {
        $auth      = $this->buildAuth();
        $directory = $auth->registerScimDirectory(data: new RegisterScimDirectoryData(
                                                            tenantSlug: 'bulk',
                                                            name      : 'Bulk Directory'
                                                        ));

        $response = $auth->runScimBulk(data: new ScimBulkRequest(
                                                 directoryId   : $directory->directory->directoryId,
                                                 directoryToken: $directory->plainTextToken,
                                                 operations    : [
                                                                     new ScimBulkOperation(
                                                                         method: 'POST',
                                                                         path  : '/Users',
                                                                         body  : [
                                                                                     'externalId' => 'bulk-1',
                                                                                     'userName'   => 'bulk-user',
                                                                                     'emails'     => [['value' => 'bulk@example.com', 'primary' => true]],
                                                                                     'groups'     => [['value' => 'users']],
                                                                                     'active'     => true,
                                                                                 ],
                                                                         bulkId: 'create-1'
                                                                     ),
                                                                     new ScimBulkOperation(
                                                                         method: 'PUT',
                                                                         path  : '/Users/bulk-1',
                                                                         body  : [
                                                                                     'userName' => 'bulk-user',
                                                                                     'emails'   => [['value' => 'bulk@example.com', 'primary' => true]],
                                                                                     'groups'   => [['value' => 'admins']],
                                                                                     'active'   => false,
                                                                                 ],
                                                                         bulkId: 'replace-1'
                                                                     ),
                                                                     new ScimBulkOperation(
                                                                         method: 'DELETE',
                                                                         path  : '/Users/bulk-1',
                                                                         bulkId: 'delete-1'
                                                                     ),
                                                                 ]
                                             ));

        $this->assertCount(expectedCount: 3, haystack: $response->operations);
        $this->assertSame(expected: 201, actual: $response->operations[0]->status);
        $this->assertSame(expected: 200, actual: $response->operations[1]->status);
        $this->assertSame(expected: 204, actual: $response->operations[2]->status);
        $this->assertSame(expected: [], actual: $auth->readScimUsers(directoryId: $directory->directory->directoryId));
    }

    /**
     * @throws RandomException
     */
    public function testScimDirectoryOutageBlocksMutationsUntilRecovered() : void
    {
        $auth      = $this->buildAuth();
        $directory = $auth->registerScimDirectory(data: new RegisterScimDirectoryData(
                                                            tenantSlug: 'outage',
                                                            name      : 'Outage Directory'
                                                        ));

        $marked = $auth->markScimDirectoryOutage(data: new MarkScimDirectoryOutageData(
                                                           directoryId: $directory->directory->directoryId,
                                                           reason     : 'maintenance'
                                                       ));

        $this->assertSame(expected: 'unavailable', actual: $marked->health->value);
        $this->assertSame(expected: 'maintenance', actual: $marked->outageReason);

        $this->expectException(ScimFailed::class);
        $this->expectExceptionMessage('SCIM directory is temporarily unavailable.');

        $auth->provisionScimUser(data: new ProvisionScimUserData(
                                           directoryId   : $directory->directory->directoryId,
                                           directoryToken: $directory->plainTextToken,
                                           externalId    : 'outage-1',
                                           email         : 'outage@example.test',
                                           username      : 'outage',
                                           groups        : [],
                                           state         : ScimAccountState::ACTIVE
                                       ));
    }

    /**
     * @throws RandomException
     */
    public function testScimDirectoryThrottleBlocksRepeatedMutations() : void
    {
        $auth      = $this->buildAuth(
            scimThrottle: new AttemptThrottle(
                              store       : new InMemoryAttemptThrottleStore(),
                              clock       : new Clock(),
                              maxAttempts : 1,
                              decaySeconds: 3600
                          )
        );
        $directory = $auth->registerScimDirectory(data: new RegisterScimDirectoryData(
                                                            tenantSlug: 'throttle',
                                                            name      : 'Throttle Directory'
                                                        ));

        $auth->provisionScimUser(data: new ProvisionScimUserData(
                                           directoryId   : $directory->directory->directoryId,
                                           directoryToken: $directory->plainTextToken,
                                           externalId    : 'throttle-1',
                                           email         : 'throttle@example.test',
                                           username      : 'throttle',
                                           groups        : [],
                                           state         : ScimAccountState::ACTIVE
                                       ));

        $this->expectException(ScimFailed::class);
        $this->expectExceptionMessage('SCIM directory operation throttled for provision.');

        $auth->provisionScimUser(data: new ProvisionScimUserData(
                                           directoryId   : $directory->directory->directoryId,
                                           directoryToken: $directory->plainTextToken,
                                           externalId    : 'throttle-2',
                                           email         : 'throttle-2@example.test',
                                           username      : 'throttle-2',
                                           groups        : [],
                                           state         : ScimAccountState::ACTIVE
                                       ));
    }
}
