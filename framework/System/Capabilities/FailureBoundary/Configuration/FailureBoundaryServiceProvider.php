<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ClassifyApplicationException\ClassifyApplicationException;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RenderApplicationError\RenderApplicationError;

/**
 * FailureBoundaryServiceProvider — registers error handling and rendering dependencies.
 */
final class FailureBoundaryServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // CreateHttpResponse — shared, stateless
        $container->singleton(CreateHttpResponse::class, static fn () : CreateHttpResponse => new CreateHttpResponse(),
        );

        // Exception classifier — stateless, can be shared
        $container->singleton(ClassifyApplicationException::class, static fn () : ClassifyApplicationException => new ClassifyApplicationException(),
        );

        // RenderApplicationError — requires both dependencies, no more new defaults
        $container->singleton(RenderApplicationError::class, static fn (ContainerInterface $c) : RenderApplicationError => new RenderApplicationError(
            createHttpResponse: $c->get(CreateHttpResponse::class),
            classifier        : $c->get(ClassifyApplicationException::class),
        ),
        );
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed for failure boundary component
    }
}
