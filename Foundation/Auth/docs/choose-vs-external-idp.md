# When To Choose Avax Auth Vs External IdP

Version: 1.0.0
Status: Normative / Local
Scope: product positioning

## Choose Avax Auth When

- you want a package-owned auth and identity kernel inside your product
- you need strong control over flows, policies, and tenant behavior
- your application already owns most of the surrounding product surface
- you want framework-neutral PHP runtime seams instead of a hosted control-plane

## Choose An External IdP When

- you need a full hosted identity platform
- you need tenant-admin UI out of the box
- you need external certification or vendor-managed compliance posture
- you need SAML brokering, email, SIEM, or enterprise IAM governance as a product

## Practical Rule

Use this package for:
- auth kernel
- identity kernel
- product-owned security flows

Do not sell it as:
- Auth0 replacement
- Okta replacement
- full hosted identity platform
