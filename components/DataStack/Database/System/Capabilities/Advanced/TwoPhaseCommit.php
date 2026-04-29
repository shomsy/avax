<?php
declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Advanced;

final class TwoPhaseCommit
{
    public function coordinate(array $participants, callable $action): void
    {
        try {
            foreach ($participants as $p) $p->prepare();
            $action();
            foreach ($participants as $p) $p->commit();
        } catch (\Throwable $e) {
            foreach ($participants as $p) $p->rollback();
            throw $e;
        }
    }
}
