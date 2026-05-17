# Laravel Governance Profile

Use this profile for Laravel applications.
Apply it together with `languages/php.md`.
Apply the matching architecture overlay from
`../../architecture/profiles/frameworks/laravel.md` when repo shape matters.

## Application Boundary Rules

- keep route files and controllers focused on transport, auth, validation
  handoff, and response shaping
- avoid putting real domain branching in route closures, middleware chains, or
  Blade templates
- prefer one obvious home for use-case orchestration such as actions, services,
  jobs, or domain modules with clear ownership
- service providers and the container should own wiring, not business decisions
- do not let facades hide hard dependencies or side effects that matter to
  review and testing

## Request, Validation, And Response Rules

- validate input before business logic through Form Requests, validators, or an
  equivalent owned boundary
- authorization must be explicit through policies, gates, or a narrow
  application contract; route protection alone is not enough
- API responses should use deliberate DTO, resource, or transformer boundaries
  when raw model exposure would leak internal shape
- keep pagination, error envelopes, and status codes stable at public HTTP
  boundaries
- do not read arbitrary request state deep inside domain logic when the boundary
  can pass explicit typed inputs

## Eloquent And Persistence Rules

- keep Eloquent models from becoming god objects that mix persistence, business
  rules, authorization, and transport behavior
- mass assignment, casts, hidden fields, and serialization behavior must be
  intentional and reviewable
- query composition should stay explicit; avoid burying core business filtering
  inside scattered global scopes without review evidence
- migrations must be deterministic, ordered, and reviewable
- seeders and factories must support safe test and bootstrap behavior without
  becoming a hidden production dependency

## Config, Bootstrap, And Runtime Rules

- read environment variables in config or bootstrap boundaries, not throughout
  application code
- config cache, route cache, and similar accelerators must remain safe to
  regenerate from canonical source
- boot should fail closed when required configuration is missing or malformed
- console entrypoints, queues, and HTTP entrypoints should all resolve the same
  owned application contracts
- keep scheduled tasks, commands, and queue workers thin at the edge and clear
  about what they delegate

## Queues, Events, And Scheduler Rules

- queued jobs must define idempotency, retry posture, timeout, and failure
  ownership explicitly
- failed jobs, queue backlog, and worker health must be visible in operational
  evidence
- events should communicate real domain or runtime facts, not replace ordinary
  function calls for local flow clarity
- scheduled tasks must be safe to rerun or guarded against duplicate impact
- release checks for serious systems should include queue failure handling and
  worker shutdown behavior

## Security And Delivery Rules

- protect sensitive actions with policies, gates, CSRF posture, and request
  validation appropriate to the surface
- keep secrets, APP keys, and environment policy strict and out of source or
  logs
- review file upload, notification, webhook, and third-party integration paths
  as security surfaces, not helper details
- test auth, validation, policy, queue, and migration paths together where the
  feature risk justifies it
- static analysis, style, and test entrypoints should be first-class parts of
  the Laravel delivery path
