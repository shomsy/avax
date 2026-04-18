<?php

declare(strict_types=1);

namespace Avax\HTTP\Request;

use Avax\Container\Providers\ServiceProvider;
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
use Avax\HTTP\Request\ServerRequest\Network\TrustedProxyPolicy;
use Override;
use Psr\Http\Message\ServerRequestInterface;

/**
 * RequestServiceProvider - Infrastructure owner for HTTP Request component.
 */
final class RequestServiceProvider extends ServiceProvider
{
    #[Override]
    public function register() : void
    {
        $this->registerInfrastructure();
        $this->registerParsers();
        $this->registerNetwork();
        $this->registerPreparer();
        $this->registerInputServices();
        $this->registerAssembler();
    }

    private function registerInfrastructure() : void
    {
        $this->app->singleton(NormalizeProtocolVersion::class);
        $this->app->singleton(NormalizeUploadedFiles::class);
        $this->app->singleton(GuardUploadedFiles::class);
    }

    private function registerParsers() : void
    {
        $this->app->singleton(ParseJsonBody::class);
        $this->app->singleton(ParseFormBody::class);
        
        $this->app->singleton(ParseBodyByContentType::class, function () {
            return new ParseBodyByContentType(
                jsonParser: $this->app->get(ParseJsonBody::class),
                formParser: $this->app->get(ParseFormBody::class)
            );
        });
    }

    private function registerNetwork() : void
    {
        $this->app->singleton(TrustedProxyPolicy::class, function () {
            return new TrustedProxyPolicy(
                trustedProxies: $this->app->config('request.trusted_proxies', [])
            );
        });

        $this->app->singleton(ParseForwardedAddresses::class);

        $this->app->singleton(ResolveClientAddress::class, function () {
            return new ResolveClientAddress(
                proxyPolicy    : $this->app->get(TrustedProxyPolicy::class),
                forwardedParser: $this->app->get(ParseForwardedAddresses::class)
            );
        });
    }

    private function registerPreparer() : void
    {
        $this->app->singleton(PrepareRequest::class, function () {
            return new PrepareRequest(
                bodyParser        : $this->app->get(ParseBodyByContentType::class),
                protocolNormalizer: $this->app->get(NormalizeProtocolVersion::class),
                filesNormalizer   : $this->app->get(NormalizeUploadedFiles::class),
                trustedProxyPolicy: $this->app->get(TrustedProxyPolicy::class),
                forwardedParser   : $this->app->get(ParseForwardedAddresses::class),
                clientResolver    : $this->app->get(ResolveClientAddress::class)
            );
        });
    }

    private function registerInputServices() : void
    {
        $this->app->singleton(InputSanitizer::class);
        $this->app->singleton(MapRequestedInputsToDto::class);
    }

    private function registerAssembler() : void
    {
        $this->app->singleton(AssembleIncomingRequest::class, function () {
            return new AssembleIncomingRequest(
                preparer : $this->app->get(PrepareRequest::class),
                sanitizer: $this->app->get(InputSanitizer::class),
                mapper   : $this->app->get(MapRequestedInputsToDto::class)
            );
        });

        $this->app->singleton(ServerRequestInterface::class, function () {
            return $this->app->get(AssembleIncomingRequest::class)->fromGlobals();
        });

        $this->app->alias(ServerRequest::class, ServerRequestInterface::class);
    }

    public function boot() : void {}
}
