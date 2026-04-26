<?php

declare(strict_types=1);

use Avax\HTTP\Security\CsrfTokens;

if (! function_exists(function: 'csrf_token')) {
    /**
     * Generates a CSRF token.
     *
     * @return string The generated token.
     *
     * @throws Exception If token generation fails.
     */
    function csrf_token() : string
    {
        $csrfManager = app(abstract: CsrfTokens::class);

        if (! $csrfManager instanceof CsrfTokens) {
            throw new RuntimeException(message: 'CsrfTokens is not registered in the container.');
        }

        return $csrfManager->getToken();
    }
}
