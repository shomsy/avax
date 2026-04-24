# ResponseEmitter

`ResponseEmitter` is the public transport boundary for sending a built response to the PHP runtime.

It delegates to the `EmitResponse` flow, which splits status, header, and body emission into separate units.
