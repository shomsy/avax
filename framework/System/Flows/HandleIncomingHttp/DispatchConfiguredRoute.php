<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Psr\Http\Message\ResponseInterface;

final readonly class DispatchConfiguredRoute
{
    public function __construct(
        private CreateHttpResponse $createHttpResponse,
        private RegisteredHttpRoutes $registeredHttpRoutes,
        private ReadIncomingHttpRequest $readIncomingHttpRequest,
        private MatchHttpRoute $matchHttpRoute,
        private RunHttpRoute $runHttpRoute,
    ) {
    }

    public function __invoke(RuntimeRequest $runtimeRequest): ResponseInterface
    {
        $serverRequest = $this->readIncomingHttpRequest->read(runtimeRequest: $runtimeRequest);

        try {
            $matchedRoute = $this->matchHttpRoute->match(
                registeredHttpRoutes: $this->registeredHttpRoutes,
                serverRequest       : $serverRequest,
            );

            return $this->runHttpRoute->run(matchedHttpRoute: $matchedRoute);
        } catch (RouteNotFoundException) {
            if ($this->registeredHttpRoutes->hasFallback()) {
                return $this->runHttpRoute->runFallback(
                    fallback      : $this->registeredHttpRoutes->fallback() ?? static fn (): string => '',
                    serverRequest : $serverRequest,
                );
            }

            return $this->createHttpResponse->error(
                message: 'Route not found',
                status : 404,
            );
        } catch (MethodNotAllowedException $exception) {
            return $this->createHttpResponse->error(
                message: $exception->getMessage(),
                status : 405,
            );
        }
    }
}
