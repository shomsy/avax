<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Avax\Components\HTTP\Response\ResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;

/**
 * PSR-15 Abstract base class for middleware that restricts access based on IP addresses.
 *
 * Concrete subclasses can define specific business logic for allowable IPs,
 * such as office IPs or other access-controlled networks.
 */
class IpRestrictionMiddleware implements MiddlewareInterface
{
    protected ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory) { $this->responseFactory = $responseFactory; }

    /**
     * PSR-15 process method: check IP restrictions before proceeding.
     *
     * @param RequestInterface        $request The incoming HTTP request.
     * @param RequestHandlerInterface $handler The next handler in the chain.
     *
     * @return ResponseInterface A response if IP is disallowed, or proceeds to the next handler.
     */
    public function process(RequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        // Extract client IP from server parameters (PSR-7 compatible)
        $clientIp     = 'unknown';
        $serverParams = method_exists($request, 'getServerParams') ? $request->getServerParams() : [];
        $forwardedFor = (string) ($serverParams['HTTP_X_FORWARDED_FOR'] ?? '');
        $clientIp     = $serverParams['REMOTE_ADDR']
            ?? ($forwardedFor !== '' ? trim(string: explode(separator: ',', string: $forwardedFor)[0]) : null)
            ?? $serverParams['HTTP_X_REAL_IP']
            ?? 'unknown';

        if (! $this->isAllowedIp(ipAddress: $clientIp)) {
            return $this->createAccessDeniedResponse();
        }

        return $handler->handle(request: $request);
    }

    /**
     * Checks if the IP address is allowed.
     *
     * @param string $ipAddress The IP address to check.
     *
     * @return bool True if the IP is allowed, false otherwise.
     */
    protected function isAllowedIp(#[SensitiveParameter] string $ipAddress) : bool
    {
        return true;
    }

    /**
     * Generates a 403 Forbidden response for disallowed IPs.
     *
     * @return ResponseInterface The access denied response.
     */
    protected function createAccessDeniedResponse() : ResponseInterface
    {
        return $this->responseFactory->createErrorResponse(
            statusCode: 403,
            message   : 'Access from your IP address is not allowed.'
        );
    }
}
