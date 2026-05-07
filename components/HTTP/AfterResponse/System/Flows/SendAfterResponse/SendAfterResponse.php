<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\AfterResponse\System\Flows\SendAfterResponse;

final readonly class SendAfterResponse
{
    /**
     * @param list<callable> $callbacks
     */
    public function send(array $callbacks) : void
    {
        fastcgi_finish_request();

        foreach ($callbacks as $callback) {
            $callback();
        }
    }
}
