<?php

declare(strict_types=1);

namespace components\HTTP\Session\SessionEvents;

final class SessionEventName
{
    public const STORED                  = 'session.stored';
    public const RETRIEVED               = 'session.retrieved';
    public const DELETED                 = 'session.deleted';
    public const CLEARED                 = 'session.cleared';
    public const ID_REGENERATED          = 'session.id_regenerated';
    public const TERMINATED              = 'session.terminated';
    public const ACTOR_BOUND             = 'session.actor_bound';
    public const SNAPSHOT_TAKEN          = 'session.snapshot_taken';
    public const SNAPSHOT_RESTORED       = 'session.snapshot_restored';
    public const STATE_EXPORTED          = 'session.state_exported';
    public const STATE_IMPORTED          = 'session.state_imported';
    public const TRANSACTION_COMMITTED   = 'session.transaction_committed';
    public const TRANSACTION_ROLLED_BACK = 'session.transaction_rolled_back';
}