<?php

declare(strict_types=1);

namespace Gemini\Cache\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;
use Psr\SimpleCache\InvalidArgumentException as CacheInvalidArgumentException;

/**
 * Class InMemoryInvalidArgumentException
 *
 * Custom exception for invalid arguments in InMemoryCache.
 */
class InMemoryInvalidArgumentException extends BaseInvalidArgumentException implements CacheInvalidArgumentException {}
