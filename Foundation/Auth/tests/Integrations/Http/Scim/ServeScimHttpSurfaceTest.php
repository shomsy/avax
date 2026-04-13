<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http\Scim;

use Avax\Auth\Integrations\Http\HttpEndpointInput;
use Avax\Auth\Integrations\Http\Scim\ServeScimHttpSurface;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Scim\RegisterDirectory\RegisterScimDirectoryData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class ServeScimHttpSurfaceTest extends TestCase
{
    public function testScimHttpSurfaceServesMetadataCrudAndPatchFlow() : void
    {
        $auth = $this->buildAuth();
        $directory = $auth->registerScimDirectory(new RegisterScimDirectoryData(
            tenantSlug  : 'acme',
            name        : 'Acme Workforce',
            groupRoleMap: ['admins' => ['admin'], 'users' => ['user']]
        ));
        $surface = new ServeScimHttpSurface($auth);
        $directoryId = $directory->directory->directoryId;
        $authHeader = ['Authorization' => 'Bearer ' . $directory->plainTextToken];

        $serviceProviderConfig = $surface->execute(new HttpEndpointInput(
            method: 'GET',
            path  : '/scim/v2/ServiceProviderConfig'
        ));
        $created = $surface->execute(new HttpEndpointInput(
            method         : 'POST',
            path           : '/scim/v2/Users',
            headers        : $authHeader,
            routeParameters: ['directoryId' => $directoryId],
            body           : [
                'externalId' => 'ext-1',
                'userName' => 'worker',
                'emails' => [['value' => 'worker@acme.test', 'primary' => true]],
                'groups' => [['value' => 'admins']],
                'active' => true,
            ]
        ));
        $patched = $surface->execute(new HttpEndpointInput(
            method         : 'PATCH',
            path           : '/scim/v2/Users/ext-1',
            headers        : $authHeader,
            routeParameters: ['directoryId' => $directoryId],
            body           : [
                'Operations' => [
                    ['op' => 'Replace', 'path' => 'groups', 'value' => [['value' => 'users']]],
                    ['op' => 'Replace', 'path' => 'active', 'value' => false],
                ],
            ]
        ));
        $listed = $surface->execute(new HttpEndpointInput(
            method         : 'GET',
            path           : '/scim/v2/Users',
            headers        : $authHeader,
            routeParameters: ['directoryId' => $directoryId],
            query          : ['filter' => 'externalId eq "ext-1"']
        ));
        $deleted = $surface->execute(new HttpEndpointInput(
            method         : 'DELETE',
            path           : '/scim/v2/Users/ext-1',
            headers        : $authHeader,
            routeParameters: ['directoryId' => $directoryId]
        ));
        $missing = $surface->execute(new HttpEndpointInput(
            method         : 'GET',
            path           : '/scim/v2/Users/ext-1',
            headers        : $authHeader,
            routeParameters: ['directoryId' => $directoryId]
        ));

        $this->assertSame(200, $serviceProviderConfig->statusCode);
        $this->assertTrue($serviceProviderConfig->body['patch']['supported']);
        $this->assertSame(201, $created->statusCode);
        $this->assertSame('ext-1', $created->body['externalId']);
        $this->assertSame(200, $patched->statusCode);
        $this->assertFalse($patched->body['active']);
        $this->assertSame('suspended', $patched->body['urn:avax:params:scim:schemas:auth:1.0:User']['state']);
        $this->assertSame(200, $listed->statusCode);
        $this->assertSame(1, $listed->body['totalResults']);
        $this->assertSame('users', $listed->body['Resources'][0]['groups'][0]['value']);
        $this->assertSame(204, $deleted->statusCode);
        $this->assertSame(404, $missing->statusCode);
    }

    public function testScimHttpSurfaceRejectsMissingDirectoryToken() : void
    {
        $surface = new ServeScimHttpSurface($this->buildAuth());

        $response = $surface->execute(new HttpEndpointInput(
            method         : 'GET',
            path           : '/scim/v2/Users',
            routeParameters: ['directoryId' => 'scim_missing']
        ));

        $this->assertSame(400, $response->statusCode);
        $this->assertSame('invalidValue', $response->body['scimType']);
    }

    private function buildAuth() : Auth
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();

        return Auth::configuration()
            ->forUser($userSource)
            ->withIdentity(new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec('scim-http-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore($refreshTokens)
            ->ready();
    }
}
