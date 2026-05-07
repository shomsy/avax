<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Foundation\Failure;

use RuntimeException;

/**
 * Thrown when a configuration file fails schema validation.
 *
 * @experimental V3 labs
 */
final class SchemaValidationException extends RuntimeException {}
