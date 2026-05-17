# Term
Middleware

# Classification
JavaScript/Node — Architectural Pattern

# Purpose
A function or module that intercepts requests, responses, or data streams to inspect, transform, or route them before they reach their final handler.

# Why Allowed
Middleware is a foundational pattern in the JavaScript and Node.js ecosystems, formalized by Express.js and adopted across the entire server-side JavaScript landscape. It describes a concrete responsibility: intercepting and processing a data flow in a pipeline. The term has precise semantics in Node.js (req/res chain, async composition, error-passing conventions) and in frontend tooling (Redux middleware, webpack dev middleware, Vite plugin middleware). It is not a generic catch-all; a real middleware must sit between two points in a request or event pipeline and either forward, transform, or short-circuit the flow.

# Allowed Contexts
- HTTP request/response pipeline (Express, Fastify, Koa, Hono)
- State management dispatch chains (Redux, Zustand middleware)
- Build tool development servers (Vite, webpack dev server middleware)
- RPC and GraphQL request interceptors
- WebSocket message interceptors
- ORM query interceptors and hooks

# Forbidden Misuse
- Naming a general-purpose utility class "Middleware" that does not intercept a pipeline
- Creating a Middleware/ folder as a dumping ground for unrelated helpers
- Calling a singleton state holder "middleware" when it never sits between two flow points
- Using "middleware" to describe standalone business logic that runs outside any request or event chain

# Ecosystem References
- https://expressjs.com/en/guide/using-middleware.html
- https://redux.js.org/understanding/middleware
- https://www.fastify.io/docs/latest/Reference/Middleware/
- https://vitejs.dev/guide/api-plugin.html#transform

# Allowed Patterns
- loggingMiddleware
- authMiddleware
- corsMiddleware
- errorHandlingMiddleware
- requestIdMiddleware
- compressionMiddleware

# Forbidden Patterns
- Middleware (as a folder name)
- GeneralMiddleware
- AppMiddleware (when it contains unrelated utilities)
- MiddlewareManager
- MiddlewareHelper
