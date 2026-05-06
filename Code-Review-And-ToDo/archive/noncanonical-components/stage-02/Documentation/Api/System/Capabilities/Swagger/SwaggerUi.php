<?php

declare(strict_types=1);

namespace Avax\Components\Documentation\Api\System\Capabilities\Swagger;

final readonly class SwaggerUi
{
    public function html(string $openApiUrl = '/api/docs/openapi.json'): string
    {
        return <<<HTML
            <!doctype html>
            <html lang="en">
            <head>
              <meta charset="utf-8">
              <meta name="viewport" content="width=device-width, initial-scale=1">
              <title>Avax API Documentation</title>
              <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
            </head>
            <body>
              <div id="swagger-ui"></div>
              <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
              <script>
                window.ui = SwaggerUIBundle({ url: "{$openApiUrl}", dom_id: "#swagger-ui" });
              </script>
            </body>
            </html>
            HTML;
    }
}
