<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Foundation\Failure;
class RequestFailure extends \RuntimeException {}

namespace Avax\Components\HTTP\Response\System\Foundation\Failure;
class ResponseFailure extends \RuntimeException {}

namespace Avax\Components\HTTP\Router\System\Foundation\Failure;
class RouterFailure extends \RuntimeException {}

namespace Avax\Components\HTTP\Middleware\System\Foundation\Failure;
class MiddlewareFailure extends \RuntimeException {}

namespace Avax\Components\HTTP\System\Foundation\Failure;
class HttpFailure extends \RuntimeException {}
