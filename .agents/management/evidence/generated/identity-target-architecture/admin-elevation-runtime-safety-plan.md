# Admin Elevation Runtime Safety Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

In scope:

- Remove hidden default `new AdminElevationStore()` from the `BeginAdminElevation` flow.
- Make admin elevation state an explicit dependency assembled at Configuration/Builder/test boundaries.
- Register admin elevation state as scoped/request-runtime state in `AccessServiceProvider`.
- Keep `Access` PublicSurface behavior stable.
- Update focused characterization tests.
- Run available static validation and classify PHP/Docker failures as ENVIRONMENT_YELLOW.

Out of scope:

- Full Tenancy admin realm redesign.
- Full AuthBuilder changes.
- Full request lifecycle integration beyond scoped provider registration.

## Expected Result

Admin elevation flow no longer creates hidden runtime state, and the provider no longer registers elevation state as a long-lived singleton.
