# Express Governance Profile

Use this profile for Express applications and APIs.

Apply it together with `languages/nodejs.md` and any relevant `typescript.md`
or `javascript.md` profile.
Apply the matching architecture overlay from
`../../architecture/profiles/frameworks/express.md` when repo shape matters.

## Routing and Middleware Rules

- keep route handlers thin and explicit
- centralize validation, auth, and error mapping middleware
- avoid hidden middleware side effects and ordering ambiguity

## Reliability Rules

- enforce request timeout and body size limits
- normalize error output and log correlation data
- separate business logic from transport concerns

## Security Rules

- apply safe defaults for headers and CORS
- validate all path, query, and body input
- protect sensitive routes with explicit authorization checks
