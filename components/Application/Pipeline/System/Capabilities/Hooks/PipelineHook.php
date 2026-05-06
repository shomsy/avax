<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\Capabilities\Hooks;

use Closure;

final readonly class PipelineHook
{
    public const string BEFORE_ROUTE = 'beforeRoute';

    public const string AFTER_ROUTE = 'afterRoute';

    public const string BEFORE_CONTROLLER = 'beforeController';

    public const string AFTER_CONTROLLER = 'afterController';

    public const string BEFORE_RESPONSE = 'beforeResponse';

    public const string AFTER_RESPONSE = 'afterResponse';

    public const string ON_EXCEPTION = 'onException';

    public const string ON_TERMINATE = 'onTerminate';

    public function __construct(
        public string $name,
        public Closure $handler,
        public int $priority = 0,
    ) {
    }
}
