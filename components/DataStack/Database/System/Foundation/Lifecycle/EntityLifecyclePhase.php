<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

/**
 * Entity lifecycle phases for database persistence operations.
 *
 * Each phase represents a specific point in the entity persistence lifecycle.
 * Listeners registered for a phase are invoked at that exact point.
 */
enum EntityLifecyclePhase: string
{
    case Creating = 'creating';
    case Created = 'created';
    case Updating = 'updating';
    case Updated = 'updated';
    case Saving = 'saving';
    case Saved = 'saved';
    case Deleting = 'deleting';
    case Deleted = 'deleted';
    case Restored = 'restored';
    case FailedToSave = 'failedToSave';
    case FailedToDelete = 'failedToDelete';
}
