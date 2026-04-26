# Pipeline

This folder owns ordered route execution.
Stages must run before middleware, middleware must run before the dispatch core, and invalid ordering fails explicitly.
