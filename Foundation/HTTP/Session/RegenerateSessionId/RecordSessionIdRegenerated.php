<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\RegenerateSessionId;

final class RecordSessionIdRegenerated
{
    private $audit;

    public function __construct($audit = null)
    {
        $this->audit = $audit;
    }

    public function handle(string $oldId, string $newId) : void
    {
        $this->audit?->record('session.id_regenerated', ['old_id' => $oldId, 'new_id' => $newId]);
    }
}