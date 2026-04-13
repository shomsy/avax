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
        $directory = $auth->registerScimDirectory(data: new RegisterScimDirectoryData(
            tenantSlug  : 'acme',
            name        : 'Acme Workforce',
            groupRoleMap: ['admins' => ['admin'], 'users' => ['user']]
        ));
        $surface = new ServeScimHttpSurface(auth: $auth);
        $directoryId = $directory->directory->directoryId;
        $authHeader = ['Authorization' => 'Bearer ' . $directory->plainTextToken];

        $serviceProviderConfig = $surface->execute(input: new HttpEndpointInput(
            method: 'GET',
            path  : '/scim/v2/ServiceProviderConfig'
        ));
        $created = $surface->execute(input: new HttpEndpointInput(
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
        $patched = $surface->execute(input: new HttpEndpointInput(
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
        $listed = $surface->execute(input: new HttpEndpointInput(
            method         : 'GET',
            path           : '/scim/v2/Users',
            headers        : $authHeader,
            routeParameters: ['directoryId' => $directoryId],
            query          : ['filter' => 'externalId eq "ext-1"']
        ));
        $deleted = $surface->execute(input: new HttpEndpointInput(
            method         : 'DELETE',
            path           : '/scim/v2/Users/ext-1',
            headers        : $authHeader,
            routeParameters: ['directoryId' => $directoryId]
        ));
        $missing = $surface->execute(input: new HttpEndpointInput(
            method         : 'GET',
            path           : '/scim/v2/Users/ext-1',
            headers        : $authHeader,
            routeParameters: ['directoryId' => $directoryId]
        ));

        $this->assertSame(expected: 200, actual: $serviceProviderConfig->statusCode);
        $this->assertTrue(condition: $serviceProviderConfig->body['patch']['supported']);
        $this->assertSame(expected: 201, actual: $created->statusCode);
        $this->assertSame(expected: 'ext-1', actual: $created->body['externalId']);
        $this->assertSame(expected: 200, actual: $patched->statusCode);
        $this->assertFalse(condition: $patched->body['active']);
        $this->assertSame(expected: 'suspended', actual: $patched->body['urn:avax:params:scim:schemas:auth:1.0:User']['state']);
        $this->assertSame(expected: 200, actual: $listed->statusCode);
        $this->assertSame(expected: 1, actual: $listed->body['totalResults']);
        $this->assertSame(expected: 'users', actual: $listed->body['Resources'][0]['groups'][0]['value']);
        $this->assertSame(expected: 204, actual: $deleted->statusCode);
        $this->assertSame(expected: 404, actual: $missing->statusCode);
    }

    public function testScimHttpSurfaceRejectsMissingDirectoryToken() : void
    {
        $surface = new ServeScimHttpSurface(auth: $this->buildAuth());

        $response = $surface->execute(input: new HttpEndpointInput(
            method         : 'GET',
            path           : '/scim/v2/Users',
            routeParameters: ['directoryId' => 'scim_missing']
        ));

        $this->assertSame(expected: 400, actual: $response->statusCode);
        $this->assertSame(expected: 'invalidValue', actual: $response->body['scimType']);
    }

    private function buildAuth() : Auth
    {
        $userSource = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();

        return Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: new Identity(jwtIdentity: new JwtIdentity(
                userSource       : $userSource,
                codec            : new HmacTokenCodec(secret: 'scim-http-secret'),
                clock            : new Clock(),
                revocationStore  : new InMemoryTokenRevocationStore(),
                refreshTokenStore: $refreshTokens
            )))
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->ready();
    }
}
