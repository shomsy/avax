<?php

declare(strict_types=1);

/**
 * ENUM representing different request options for HTTP requests.
 *
 * This enum is used to clearly define and enforce the allowed request
 * options within the Avax HTTP client implementation. The values within
 * this ENUM are used to standardize the keys for various request parameters.
 */

namespace Avax\HTTP\Enums;

/**
 * Enum RequestOption
 *
 * Enum class representing different types of request options.
 * Each member of the enum stands for a specific way in which request data
 * can be formatted or processed, tailored for different use cases.
 */
enum RequestOption: string
{
    case HEADERS = 'headers';

    case JSON = 'json';
}
