<?php

declare(strict_types=1);

namespace Avax\DataLayer\QueryStoredData;

use RuntimeException;

/**
 * DataQueryFailure - reports invalid query shape or execution failure.
 */
final class DataQueryFailure extends RuntimeException {}
