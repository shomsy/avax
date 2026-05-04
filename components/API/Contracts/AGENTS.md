# API Contracts Component

**Version:** 1.0.0
**Status:** experimental
**Purpose:** Enterprise-grade API contract discipline with OpenAPI support

## PublicSurface

```php
use Avax\API\Contracts\ApiContracts;
use Avax\API\Contracts\ApiContract;
use Avax\API\Contracts\ApiContractReport;
```

## Flows

- `DescribeHttpContracts/` - Document HTTP API contracts
- `ValidateApiContracts/` - Validate contract implementations
- `DetectBreakingApiChanges/` - Detect breaking changes
- `GenerateApiContractTests/` - Generate contract tests

## Capabilities

- `RequestContracts/` - Request DTO contracts
- `ResponseContracts/` - Response DTO contracts
- `EndpointContracts/` - Endpoint contracts
- `AuthContracts/` - Authentication contracts
- `BreakingChanges/` - Breaking change detection