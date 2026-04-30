<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Advanced;

use Throwable;

final class TwoPhaseCommit
{
    public function coordinate(array $participants, callable $action) : void
    {
        try {
            foreach ($participants as $p) {
                $p->prepare();
            }

            $action();
            foreach ($participants as $participant) {
                $participant->commit();
            }
        } catch (Throwable $throwable) {
            foreach ($participants as $participant) {
                $participant->rollback();
            }

            throw $throwable;
        }
    }
}
