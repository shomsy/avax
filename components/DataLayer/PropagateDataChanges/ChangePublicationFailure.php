<?php

declare(strict_types=1);

namespace components\DataLayer\PropagateDataChanges;

use RuntimeException;

/**
 * ChangePublicationFailure - reports failed change publication.
 */
final class ChangePublicationFailure extends RuntimeException {}
