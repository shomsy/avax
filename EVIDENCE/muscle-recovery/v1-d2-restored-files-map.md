# V1-D2 Restored Files Map

## HTTP/Request (39 files)

```
components/HTTP/Request/System/
├── PublicSurface/
│   ├── Request.php ✓
│   └── RequestInterface.php ✓
├── Flows/
│   ├── MapRuntimeRequest/
│   ├── PrepareRequest/
│   └── ReadRequest/
├── Capabilities/
│   ├── Method/
│   ├── URI/
│   ├── Headers/
│   ├── Body/
│   ├── Inputs/
│   ├── Cookies/
│   ├── Files/
│   ├── Attributes/
│   └── Network/
├── Configuration/
└── Foundation/
    ├── Values/
    └── Failure/
```

## HTTP/Response (24 files)

```
components/HTTP/Response/System/
├── PublicSurface/
│   ├── Response.php ✓
│   ├── ResponseInterface.php ✓
│   ├── Responses.php ✓
│   └── shortcuts.php ✓
├── Flows/
│   ├── BuildResponse/
│   ├── BuildJsonResponse/
│   ├── BuildRedirectResponse/
│   └── EmitResponse/
├── Capabilities/
│   ├── Headers/
│   ├── Body/
│   ├── Status/
│   ├── Json/
│   └── Emitters/
├── Configuration/
└── Foundation/
    ├── Values/
    └── Failure/
```

## HTTP/Security (10 files)

```
components/HTTP/Security/System/
├── PublicSurface/
│   ├── Security.php ✓
│   └── shortcuts.php ✓
├── Flows/
│   ├── ApplySecurityHeaders/
│   └── VerifyCsrfToken/
├── Capabilities/
│   ├── Csrf/
│   ├── Headers/
│   ├── TrustedProxy/
│   └── TrustedHost/
├── Configuration/
└── Foundation/
    ├── Values/
    └── Failure/
```

---

## Restoration Complete

All three components are present with production code.
Backup code from avax-backup.txt was used as source material.