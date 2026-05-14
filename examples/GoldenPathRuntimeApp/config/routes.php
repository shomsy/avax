<?php

declare(strict_types=1);

use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;

$responseFactory = new ResponseFactory();

/**
 * Route definitions for the Webhook Ingestion Pipeline.
 *
 * The router uses exact URI match only — no {id} parameter extraction.
 * Route actions must return a ResponseInterface (PSR-7).
 */
return static function (RouterInterface $router) use ($responseFactory) : void {
    $router->get('/health', static fn () => $responseFactory->json([
                                                                       'status'  => 'healthy',
                                                                       'service' => 'webhook-ingestion-pipeline',
                                                                   ]));

    $router->post('/webhooks/ingest', static function (ServerRequestInterface $request) use ($responseFactory) {
        $body   = (string) $request->getBody();
        $parsed = json_decode($body, true);

        if (! is_array($parsed)) {
            return $responseFactory->json(['status' => 'error', 'message' => 'Invalid JSON body'], 400);
        }

        $webhookId = bin2hex(random_bytes(8));
        $source    = $parsed['source'] ?? 'unknown';

        return $responseFactory->json([
                                          'status'     => 'accepted',
                                          'webhook_id' => $webhookId,
                                          'source'     => $source,
                                      ]);
    });

    $router->get('/webhooks/status', static function (ServerRequestInterface $request) use ($responseFactory) {
        $queryParams = $request->getQueryParams();
        $webhookId   = $queryParams['id'] ?? 'unknown';

        return $responseFactory->json([
                                          'webhook_id' => $webhookId,
                                          'status'     => 'processed',
                                          'state'      => 'completed',
                                      ]);
    });
};
