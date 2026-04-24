# ResolveRequest

This flow maps an incoming `ServerRequest` to a `RouteResolutionContext`.
It owns method/path/domain matching, parameter extraction, constraint validation, HEAD fallback, and request parameter
injection.
