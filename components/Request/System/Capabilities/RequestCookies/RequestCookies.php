<?php

declare(strict_types=1);

namespace Avax\Components\Request\System\Capabilities\RequestCookies;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestCookies\RequestCookies as RealRequestCookies;

/**
 * RequestCookies — delegates to the real implementation.
 */
class RequestCookies extends RealRequestCookies {}
