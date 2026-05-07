<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\System\Flows\BindSessionActor;

use Avax\Components\HTTP\Session\System\System\Foundation\SessionActor;
use Avax\Components\HTTP\Session\System\System\PublicSurface\Session;

final readonly class BindSessionActor
{
    public function __construct(
        private Session $session,
    ) {}

    public function handle(SessionActor $sessionActor) : void
    {
        $this->session->put('_actor', [
            'id'   => $sessionActor->id,
            'data' => $sessionActor->data,
        ]);

        $this->session->regenerate(); // Prevent session fixation on login
    }
}
