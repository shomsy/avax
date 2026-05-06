<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\BackChannelLogout\BackChannelLogout;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\BackChannelLogout\BackChannelLogoutData;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\FrontChannelLogout\FrontChannelLogout;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\FrontChannelLogout\FrontChannelLogoutData;

final readonly class Logout
{
    public function __construct(private FrontChannelLogout $frontChannelLogout, private BackChannelLogout $backChannelLogout)
    {
    }

    public function execute(LogoutData $logoutData): LogoutResult
    {
        if ($logoutData->logoutToken !== null && trim(string: $logoutData->logoutToken) !== '') {
            return $this->backChannelLogout->execute(data: new BackChannelLogoutData(logoutToken: $logoutData->logoutToken));
        }

        return $this->frontChannelLogout->execute(data: new FrontChannelLogoutData(
            sessionId            : $logoutData->sessionId,
            idTokenHint          : $logoutData->idTokenHint,
            postLogoutRedirectUri: $logoutData->postLogoutRedirectUri,
            state                : $logoutData->state,
        ));
    }
}
