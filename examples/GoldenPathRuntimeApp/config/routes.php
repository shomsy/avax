<?php

declare(strict_types=1);

use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Route definitions for the Webhook Ingestion Pipeline.
 *
 * The router uses exact URI match only — no {id} parameter extraction.
 * Route actions must return a ResponseInterface (PSR-7).
 *
 * @param RouterInterface $router
 * @param CreateHttpResponse $createHttpResponse
 */
return static function (RouterInterface $router, CreateHttpResponse $createHttpResponse): void {
    $responses = new Responses(createHttpResponse: $createHttpResponse);

    $router->get('/health', static fn () => $responses->json([
                                                                       'status'  => 'healthy',
                                                                       'service' => 'webhook-ingestion-pipeline',
                                                                   ]));

    $router->post('/webhooks/ingest', static function (ServerRequestInterface $request) use ($responses) {
        $body   = (string) $request->getBody();
        $parsed = json_decode($body, true);

        if (! is_array($parsed)) {
            return $responses->json(['status' => 'error', 'message' => 'Invalid JSON body'], 400);
        }

        $webhookId = bin2hex(random_bytes(8));
        $source    = $parsed['source'] ?? 'unknown';

        return $responses->json([
                                          'status'     => 'accepted',
                                          'webhook_id' => $webhookId,
                                          'source'     => $source,
                                      ]);
    });

    $router->get('/webhooks/status', static function (ServerRequestInterface $request) use ($responses) {
        $queryParams = $request->getQueryParams();
        $webhookId   = $queryParams['id'] ?? 'unknown';

        return $responses->json([
                                          'webhook_id' => $webhookId,
                                          'status'     => 'processed',
                                          'state'      => 'completed',
                                      ]);
    });
};
