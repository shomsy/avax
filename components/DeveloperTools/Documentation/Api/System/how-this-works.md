---
title: System-how-this-works
owner: api-documentation
last_reviewed: 2026-04-30
classification: public
---

# Api Documentation System How This Works

## What this folder is

This system owns API documentation generation. It turns route metadata into OpenAPI and exposes Swagger UI HTML for
`/api/docs`.

## Real commands or triggers that reach this folder

The route file calls `ApiDocumentation::openApi()` for `/api/docs/openapi.json` and `ApiDocumentation::swagger()` for
`/api/docs`.

## Exact upstream handoffs

`ApiDocumentation` receives route metadata and delegates OpenAPI shape creation to `OpenApiGenerator` and HTML rendering
to `SwaggerUi`.

## Failure behavior

Unknown route metadata is omitted. The generator always returns a valid OpenAPI document skeleton.
