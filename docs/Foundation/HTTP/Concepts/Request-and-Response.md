# Request and Response

Request assembly and response creation are separate ownership axes.

- `Request/*` owns `ServerRequest` creation, typed input access, proxy handling, and DTO attachment.
- `Response/*` owns PSR-7 response construction and stream behavior.

Keeping them separate avoids the old pattern where controllers and middleware had to guess which layer owned
serialization or input normalization.
