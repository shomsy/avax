# Body

The body capability separates payload encoding from message state.

- `NormalizeResponseBody` turns scalars, resources, and streams into a canonical response body
- `Json`, `Xml`, and `Problem` subfolders own specialized encoders
- builders choose the encoder they need and then hand the finished payload to `BuildResponse`
