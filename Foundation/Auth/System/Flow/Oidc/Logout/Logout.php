<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\Logout;

use Avax\Auth\System\Flow\Oidc\BackChannelLogout\BackChannelLogout;
use Avax\Auth\System\Flow\Oidc\BackChannelLogout\BackChannelLogoutData;
use Avax\Auth\System\Flow\Oidc\FrontChannelLogout\FrontChannelLogout;
use Avax\Auth\System\Flow\Oidc\FrontChannelLogout\FrontChannelLogoutData;

final readonly class Logout
{
    public function __construct(
        private FrontChannelLogout $frontChannelLogout,
        private BackChannelLogout $backChannelLogout
    ) {}

    public function execute(LogoutData $data) : LogoutResult
    {
        if ($data->logoutToken !== null && trim($data->logoutToken) !== '') {
            return $this->backChannelLogout->execute(data: new BackChannelLogoutData(logoutToken: $data->logoutToken));
        }

        return $this->frontChannelLogout->execute(data: new FrontChannelLogoutData(
            sessionId              : $data->sessionId,
            idTokenHint            : $data->idTokenHint,
            postLogoutRedirectUri  : $data->postLogoutRedirectUri,
            state                  : $data->state
        ));
    }
}
