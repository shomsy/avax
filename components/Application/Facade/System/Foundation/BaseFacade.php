<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\Foundation;

use Psr\Container\ContainerInterface;
use RuntimeException;

class BaseFacade
{
    protected static ?ContainerInterface $container = null;

    protected static ?string $accessor = null;

    /** @var array<string, object> */
    protected static array $resolvedInstances = [];

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function clearAllResolvedInstances(): void
    {
        self::$resolvedInstances = [];
    }

    protected static function getContainer(): ContainerInterface
    {
        if (self::$container === null) {
            throw new RuntimeException('Container not set. Call BaseFacade::setContainer() first.');
        }

        return self::$container;
    }

    protected static function getAccessor(): string
    {
        if (self::$accessor === null) {
            throw new RuntimeException('Facade accessor not set.');
        }

        return self::$accessor;
    }

    protected static function resolve(): object
    {
        $accessor = self::getAccessor();

        if (! isset(self::$resolvedInstances[$accessor])) {
            self::$resolvedInstances[$accessor] = self::getContainer()->get($accessor);
        }

        return self::$resolvedInstances[$accessor];
    }
}
