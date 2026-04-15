<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Tenant;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\Tenant\TenantMemberRole;
use Avax\Auth\System\Capability\Tenant\TenantMemberState;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Tenant\AcceptInvite\AcceptTenantInviteData;
use Avax\Auth\System\Flow\Tenant\CreateTenant\CreateTenantData;
use Avax\Auth\System\Flow\Tenant\InviteMember\InviteTenantMemberData;
use Avax\Auth\System\Flow\Tenant\RemoveMember\RemoveTenantMemberData;
use Avax\Auth\System\Flow\Tenant\SuspendMember\SuspendTenantMemberData;
use Avax\Auth\System\Flow\Tenant\TenantFailed;
use Avax\Auth\System\Flow\Tenant\TransferOwnership\TransferTenantOwnershipData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class TenantFlowTest extends TestCase
{
    public function testTenantCreateInviteAcceptSuspendTransferAndRemoveFlow() : void
    {
        [$auth] = $this->buildAuth();

        $tenant      = $auth->createTenant(data: new CreateTenantData(
                                                     slug       : 'acme',
                                                     name       : 'Acme',
                                                     ownerUserId: 1
                                                 ));
        $invite      = $auth->inviteTenantMember(data: new InviteTenantMemberData(
                                                           tenantSlug: 'acme',
                                                           email     : 'member@example.com',
                                                           role      : TenantMemberRole::ADMIN,
                                                           invitedBy : 'owner@example.com'
                                                       ));
        $member      = $auth->acceptTenantInvite(data: new AcceptTenantInviteData(
                                                           inviteToken: $invite->plainTextToken,
                                                           userId     : 2
                                                       ));
        $members     = $auth->readTenantMembers(tenantSlug: 'acme');
        $suspended   = $auth->suspendTenantMember(data: new SuspendTenantMemberData(
                                                            tenantSlug: 'acme',
                                                            userId    : 2
                                                        ));
        $transferred = $auth->transferTenantOwnership(data: new TransferTenantOwnershipData(
                                                                tenantSlug    : 'acme',
                                                                newOwnerUserId: 2
                                                            ));
        $auth->removeTenantMember(data: new RemoveTenantMemberData(
                                            tenantSlug: 'acme',
                                            userId    : 1
                                        ));
        $remainingMembers = $auth->readTenantMembers(tenantSlug: 'acme');

        $this->assertSame(expected: 'acme', actual: $tenant->slug);
        $this->assertSame(expected: TenantMemberRole::ADMIN, actual: $member->role);
        $this->assertCount(expectedCount: 2, haystack: $members);
        $this->assertSame(expected: TenantMemberState::SUSPENDED, actual: $suspended->state);
        $this->assertSame(expected: 2, actual: $transferred->ownerUserId);
        $this->assertCount(expectedCount: 1, haystack: $remainingMembers);
        $this->assertSame(expected: 2, actual: $remainingMembers[0]->userId);
        $this->assertSame(expected: TenantMemberRole::OWNER, actual: $remainingMembers[0]->role);
    }

    /**
     * @return array{Auth, InMemoryUserSource}
     */
    private function buildAuth() : array
    {
        $userSource = new InMemoryUserSource();
        $userSource->create(user: User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'owner@example.com'),
            username    : 'owner',
            passwordHash: 'hash'
        ));
        $userSource->create(user: User::create(
            id          : new UserId(value: 2),
            email       : new UserEmail(value: 'member@example.com'),
            username    : 'member',
            passwordHash: 'hash'
        ));
        $userSource->create(user: User::create(
            id          : new UserId(value: 3),
            email       : new UserEmail(value: 'other@example.com'),
            username    : 'other',
            passwordHash: 'hash'
        ));
        $refreshTokens = new InMemoryRefreshTokenStore();

        $auth = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                                                                   userSource       : $userSource,
                                                                   codec            : new HmacTokenCodec(secret: 'tenant-secret'),
                                                                   clock            : new Clock(),
                                                                   revocationStore  : new InMemoryTokenRevocationStore(),
                                                                   refreshTokenStore: $refreshTokens
                                                               )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->ready();

        return [$auth, $userSource];
    }

    public function testTenantInviteRejectsWrongUserEmail() : void
    {
        [$auth] = $this->buildAuth();

        $auth->createTenant(data: new CreateTenantData(
                                      slug       : 'acme',
                                      name       : 'Acme',
                                      ownerUserId: 1
                                  ));
        $invite = $auth->inviteTenantMember(data: new InviteTenantMemberData(
                                                      tenantSlug: 'acme',
                                                      email     : 'member@example.com',
                                                      role      : TenantMemberRole::MEMBER,
                                                      invitedBy : 'owner@example.com'
                                                  ));

        $this->expectException(TenantFailed::class);
        $this->expectExceptionMessage('Tenant invite email does not match the accepting user.');

        $auth->acceptTenantInvite(data: new AcceptTenantInviteData(
                                            inviteToken: $invite->plainTextToken,
                                            userId     : 3
                                        ));
    }
}
