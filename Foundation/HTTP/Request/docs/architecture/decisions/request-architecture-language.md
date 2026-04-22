# ADR: Request Architecture Language Freeze

## Situacija (Context)

Trenutni HTTP Request nasleđuje prekomplotnu bazu `AbsoluteServerRequest`, tesno je spojen sa sistemom superglobala,
ujedno služi kao state owner ali drži i interpretaciju (npr. parseovanog headera/JSON body-ja) posredstvom višestrukih
trait-ova. Naming je često bio pomešan ili maskiran bucket rečima (Helpers, Utils, Base).
U cilju uspostavljanja čiste _Screaming/Fractal Flow_ strukture, neophodno je standardizovati i "zamrznuti" rečnik, kako
se ubuduće tehnički razvoji ne bi rasplinuli.

## Odluka (Decision)

Odlučili smo da trajno uspostavimo mapiranu _flow_ arhitekturu prema sledećem rečniku i rasporedu odgovornosti.
Namespace system root za ovu komponentu je mapiran unutar `Avax\HTTP\Request\ServerRequest\IncomingRequest\` (odnosno
odgovarajući folder unutar Foundation/HTTP/Request).

- **Flow**: `ServerRequest`
- **Subflow**: `IncomingRequest`
- **State Owner**: `ServerRequest` (puna PSR-7 implementacija)
- **Legacy Bridge**: `Request` (podržava `input()`, `all()` za compatibility)
- **Shared State/Capability Owners**:
    - `RequestHeaders` (rukovodi `list<string>` tipovima header-a)
    - `RequestedInputs` (DSL-only accessor nad inputom: `bool()`, `int()`, `text()`, `list()`, `float()`)
    - `RequestBody` i `ParsedBody` (striknto razdvojeni stream status vs. interpretacija)
    - `RequestCookies`, `RequestAttributes`, `UploadedFiles`, `RequestSession`.
- **Action Owners**:
    - `NormalizeHeaders`, `ReadRequestTarget`, `NormalizeProtocolVersion`
    - `ParseBodyByContentType`, `ParseJsonBody`, `ParseFormBody`
    - `NormalizeUploadedFiles`, `GuardUploadedFiles`
    - `BearerTokenReader`, `ResolveClientAddress`, `TrustedProxyPolicy`, `ParseForwardedAddresses`
- **Configuration Owners**:
    - `AssembleIncomingRequest`, `PrepareRequest`

Istovremeno **ZABRANJUJEMO** dalju primenu:

- `AbsoluteServerRequest`, `BaseRequest`, `RequestHelper`, `RequestManager`
- Korišćenja bilo kakvih `ParameterBag` varijanti kao jedinstvenog proxy/storage modela
- Smeštanja core request ponašanja u trait-ove (trait-sprawl).

## Posledice (Consequences)

Ovaj jezik direktno diktira stablo direktorijuma. Sve buduće klase u ovom namespace-u moraće da se smeste isključivo u
neki od podfoldera koji jasno nosi priču iz _Flow_ / _Capability_ domena. Imena kao što su "Service" ili "Manager"
bivaju napuštena zauvek u korist jasnih akcija (`Read...`, `Parse...`, `Normalize...`, `Resolve...`).
