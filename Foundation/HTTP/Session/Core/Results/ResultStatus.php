<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core\Results;

/**
 * ResultStatus Enum
 *
 * Represents the status of an action result.
 *
 * @package Avax\HTTP\Session\Core\Results
 */
enum ResultStatus: string
{
    case SUCCESS = 'success';
    case FAILURE = 'failure';
    case RETRYABLE = 'retryable';
}
