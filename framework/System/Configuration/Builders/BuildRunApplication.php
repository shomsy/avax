<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\Builders;

use Avax\Components\Application\Container\System\Capabilities\ResolveCallable\ResolveCallable;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ActionResolution\ControllerResolver;
use Avax\Components\HTTP\Dispatcher\System\Capabilities\ArgumentResolution\ArgumentResolver;
use Avax\Components\HTTP\Router\System\Flows\MatchRoute\MatchRoute;
use Avax\Components\HTTP\SecureRequest\System\Capabilities\ResolveSecureRequest\SecureRequestInputBuilder;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\MetricsCollector;
use Avax\Framework\System\Capabilities\ResponseNormalization\NormalizeControllerResult;
use Avax\Framework\System\Flows\HandleIncomingHttp\MatchHttpRoute;
use Avax\Framework\System\Flows\HandleIncomingHttp\ReadIncomingHttpRequest;
use Avax\Framework\System\Flows\HandleIncomingHttp\RouteFacadeContainer;
use Avax\Framework\System\Flows\RunApplication\RunApplication;

final readonly class BuildRunApplication
{
    public static function fromDefaultResolutionPipeline(
        CreateHttpResponse $createHttpResponse,
        NormalizeControllerResult $normalizer,
        MetricsCollector|null $metricsCollector = null,
    ): RunApplication {
        $container          = new RouteFacadeContainer();
        $resolveCallable    = new ResolveCallable(container: clone $container);
        $controllerResolver = new ControllerResolver(resolver: $resolveCallable);
        $argumentResolver = new ArgumentResolver(container: $container, inputBuilder: new SecureRequestInputBuilder());

        return new RunApplication(
            createHttpResponse: $createHttpResponse,
            normalizer        : $normalizer,
            readRequest       : new ReadIncomingHttpRequest(),
            matchRoute        : new MatchHttpRoute(new MatchRoute()),
            controllerResolver: $controllerResolver,
            argumentResolver  : $argumentResolver,
            metricsCollector  : $metricsCollector,
        );
    }
}
