<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Foundation\Exception;

use RuntimeException;

/**
 * Thrown when a permission check fails during authorization.
 */
final class PermissionDeniedException extends RuntimeException {}
