<?php

declare(strict_types=1);

use Avax\HTTP\Security\CsrfTokenManager;

if (! function_exists(function: 'csrf_token')) {
    /**
     * Get the current CSRF token.
     *
     * @return string
     * @throws Exception
     */
    function csrf_token() : string
    {
        $csrfManager = app(abstract: CsrfTokenManager::class);

        if (! $csrfManager instanceof CsrfTokenManager) {
            throw new RuntimeException(message: 'CsrfTokenManager is not registered in the container.');
        }

        return $csrfManager->getToken();
    }
}
