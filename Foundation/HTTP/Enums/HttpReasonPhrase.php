<?php

declare(strict_types=1);

namespace Avax\HTTP\Enums;

/**
 * Enum representing HTTP Reason Phrases associated with Status Codes.
 */
enum HttpReasonPhrase: string
{
    case OK                  = 'OK';

    case CREATED             = 'Created';

    case ACCEPTED            = 'Accepted';

    case NO_CONTENT          = 'No Content';

    case MOVED_PERMANENTLY   = 'Moved Permanently';

    case FOUND               = 'Found';

    case NOT_MODIFIED        = 'Not Modified';

    case BAD_REQUEST         = 'Bad Request';

    case UNAUTHORIZED        = 'Unauthorized';

    case FORBIDDEN           = 'Forbidden';

    case NOT_FOUND           = 'Not Found';

    case METHOD_NOT_ALLOWED  = 'Method Not Allowed';

    case INTERNAL_SERVER_ERROR = 'Internal Server Error';

    case NOT_IMPLEMENTED     = 'Not Implemented';

    case BAD_GATEWAY         = 'Bad Gateway';

    case SERVICE_UNAVAILABLE = 'Service Unavailable';

    case GATEWAY_TIMEOUT     = 'Gateway Timeout';
}
