<?php
declare(strict_types=1);

namespace Foundation\HTTP\Session\Recovery;

use Foundation\HTTP\Session\Storage\Store;

final class SessionMigrator
{
    public function __construct(private Store $source, private Store $target) {}

    public function migrate() : int
    {
        $count = 0;
        foreach ($this->source->all() as $key => $value) {
            $this->target->put($key, $value);
            $count++;
        }

        return $count;
    }
}
