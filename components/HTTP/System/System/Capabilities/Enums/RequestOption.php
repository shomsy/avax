<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\System\Capabilities\Enums;

enum RequestOption: string
{
    case HEADERS         = 'headers';
    case BODY            = 'body';
    case JSON            = 'json';
    case QUERY           = 'query';
    case TIMEOUT         = 'timeout';
    case CONNECT_TIMEOUT = 'connect_timeout';
    case AUTH            = 'auth';
    case VERIFY          = 'verify';
    case CERT            = 'cert';
    case PROXY           = 'proxy';
    case ALLOW_REDIRECTS = 'allow_redirects';
    case SINK            = 'sink';
    case STREAM          = 'stream';
    case VERSION         = 'version';
    case DEBUG           = 'debug';
}
