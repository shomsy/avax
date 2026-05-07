# API OpenAPI

The OpenAPI component turns an `ApiSurfaceDefinition` into OpenAPI 3.1 operator artifacts. It owns document generation,
validation, comparison, JSON rendering, and YAML rendering.

It does not discover framework routes by itself yet. The current input is the API Surface snapshot from
`components/API/Surface`.

## Public Flow

The public trigger is `OpenAPI::fromSurface($surface)`.

1. `GenerateOpenApiDocument` receives an API surface definition.
2. `BuildOpenApiDocument` maps endpoints, payload schemas, authentication requirements, and error schemas into OpenAPI.
3. `ValidateOpenApiDocument` verifies the generated document shape.
4. `CompareOpenApiDocuments` reports removed or changed operations.
5. `RenderOpenApiJson` and `RenderOpenApiYaml` emit operator artifacts.
