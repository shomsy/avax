<?php

declare(strict_types=1);

namespace Avax\HTTP\Request;

use Avax\Container\DI\Capabilities\Declaration\Providers\ServiceProviderInterface;
use Avax\Container\DI\ContainerInterface;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\AssembleIncomingRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\GuardUploadedFiles;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\HTTP\Request\ServerRequest\Network\ParseForwardedAddresses;
use Avax\HTTP\Request\ServerRequest\Network\ResolveClientAddress;
use Avax\HTTP\Request\ServerRequest\Network\TrustedIpv4ProxyPolicy;
use Psr\Http\Message\ServerRequestInterface;

/**
 * RequestServiceProvider
 *
 * Registers infrastructure and request-assembly services for the HTTP request flow.
 *
 * Lifetime rules:
 * - singleton: stateless, shareable collaborators
 * - scoped: request-local runtime state
 */
final readonly class RequestServiceProvider implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app) {}

    public function dependsOn() : array
    {
        return [];
    }

    public function register() : void
    {
        $this->registerInfrastructure();
        $this->registerNetwork();
        $this->registerInputServices();
        $this->registerAssembly();
        $this->registerRuntimeRequest();
    }

    private function registerInfrastructure() : void
    {
        $this->app->singleton(abstract: NormalizeProtocolVersion::class);
        $this->app->singleton(abstract: NormalizeUploadedFiles::class);
        $this->app->singleton(abstract: GuardUploadedFiles::class);

        $this->app->singleton(abstract: ParseJsonBody::class);
        $this->app->singleton(abstract: ParseFormBody::class);
        $this->app->singleton(abstract: ParseBodyByContentType::class);
    }

    private function registerNetwork() : void
    {
        $this->app->singleton(abstract: TrustedIpv4ProxyPolicy::class, concrete: function () {
            $trustedProxies = [];

            if ($this->app->has(id: 'config')) {
                $config         = $this->app->get(id: 'config');
                $trustedProxies = method_exists(object_or_class: $config, method: 'get') ? $config->get('request.trusted_proxies', []) : [];
            }

            return new TrustedIpv4ProxyPolicy(
                trustedProxies: is_array(value: $trustedProxies) ? $trustedProxies : [],
            );
        });

        $this->app->singleton(abstract: ParseForwardedAddresses::class);
        $this->app->singleton(abstract: ResolveClientAddress::class);
    }

    private function registerInputServices() : void
    {
        $this->app->singleton(abstract: InputSanitizer::class);
        $this->app->singleton(abstract: MapRequestedInputsToDto::class);
    }

    private function registerAssembly() : void
    {
        $this->app->singleton(abstract: PrepareRequest::class);
        $this->app->singleton(abstract: AssembleIncomingRequest::class);
    }

    private function registerRuntimeRequest() : void
    {
        $this->app->scoped(abstract: ServerRequest::class, concrete: function () {
            return $this->app->get(id: AssembleIncomingRequest::class)->fromGlobals();
        });

        $this->app->alias(alias: ServerRequestInterface::class, abstract: ServerRequest::class);
    }

    public function boot() : void {}
}