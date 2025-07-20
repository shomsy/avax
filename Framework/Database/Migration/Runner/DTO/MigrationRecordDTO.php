<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\DTO;

use Gemini\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Gemini\DataHandling\Validation\Attributes\Rules\Integer;
use Gemini\DataHandling\Validation\Attributes\Rules\Required;
use Gemini\DataHandling\Validation\Attributes\Rules\StringType;

/**
 * DTO representing a single migration record.
 *
 * Used to transfer structured migration metadata (name, SQL, batch, time).
 */
final class MigrationRecordDTO extends AbstractDTO
{
    #[Required(message: 'Migration name is required.')]
    #[StringType(message: 'Migration must be a string.')]
    public string $migration;

    #[Required(message: 'Executable is required.')]
    #[StringType(message: 'Executable must be a string.')]
    public string $executable;

    #[Required(message: 'Batch ID is required.')]
    #[Integer(message: 'Batch must be an integer.')]
    public int    $batch;

    #[Required(message: 'Execution time is required.')]
    #[StringType(message: 'Execution time must be a valid datetime string.')]
    public string $executed_at;
}
