<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

use RuntimeException;

/**
 * ChangePublicationFailure - reports failed change publication.
 */
final class ChangePublicationFailure extends RuntimeException {}
