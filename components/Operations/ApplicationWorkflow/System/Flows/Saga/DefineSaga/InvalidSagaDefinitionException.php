<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

use RuntimeException;
use Throwable;

/**
 * Thrown when a saga definition fails validation.
 */
final class InvalidSagaDefinitionException extends RuntimeException
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(
        array $errors,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: 'Invalid saga definition: '.implode('; ', $errors),
            code: $code,
            previous: $previous,
        );
    }
}
