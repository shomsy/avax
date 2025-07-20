<?php

declare(strict_types=1);

namespace Gemini\HTTP\Session\Exceptions;

use RuntimeException;

/**
 * Exception thrown when attempting to retrieve a non-existent flash key.
 */
final class FlashBagKeyNotFoundException extends RuntimeException {}