<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\HTTP\Response\ResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * PSR-15 Abstract base class for middleware that restricts access based on IP addresses.
 *
 * Concrete subclasses can define specific business logic for allowable IPs,
 * such as office IPs or other access-controlled networks.
 */
abstract class IpRestrictionMiddleware implements MiddlewareInterface
{
    public function __construct(protected ResponseFactory $responseFactory) {}

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
        $clientIp = 'unknown';
        if ($request instanceof ServerRequestInterface) {
            $serverParams = $request->getServerParams();
            $clientIp     = $serverParams['REMOTE_ADDR'] ??
                $serverParams['HTTP_X_FORWARDED_FOR'] ??
                $serverParams['HTTP_X_REAL_IP'] ??
                'unknown';
        }

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
    abstract protected function isAllowedIp(string $ipAddress) : bool;

    /**
     * Generates a 403 Forbidden response for disallowed IPs.
     *
     * @return ResponseInterface The access denied response.
     */
    protected function createAccessDeniedResponse() : ResponseInterface
    {
        return $this->responseFactory->view(
            template: 'errors.403',
            data    : ['message' => 'Access from your IP address is not allowed.'],
            status  : 403
        );
    }
}
