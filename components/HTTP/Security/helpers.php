<?php
declare(strict_types=1);

use Avax\Components\HTTP\Security\System\PublicSurface\Security;

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        // This assumes a global app() helper exists as per backup
        return app(Security::class)->csrfToken();
    }
}
