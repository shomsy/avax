# Architecture

The HTTP component is organized around runtime boundaries, not generic helper buckets.

Entry flow:

1. request assembly
2. global middleware pipeline
3. router runtime resolution
4. controller execution inside Router
5. response shaping
6. optional session/security side effects
