# ADR: PSR Surface vs Internal Ownership

## Situacija (Context)

Zahtevi da se framework uskladi sa PSR-7 standardima (kako bi omogućili interoperabilnost) vrlo lako dovode do
takozvanog "PSR curenja" – gde PSR-7 metode poput `withHeader`, `getParsedBody`, `getUploadedFiles` kreiraju veliku
količinu teške logike unutar "root" `Request` klase (ili bazne klase kroz preteranu kolicinu traitova, kao sto je ovde
slucaj sa `InputManagementTrait`, itd.). Tako request objuekti završavaju i kao State owneri i kao action / rule owneri.

## Odluka (Decision)

Zadržavamo punu PSR-7 kompatibilnost za sve eksterne posmatrače sistema. Ipak, interni "ownership" nad stanjem Request-a
neće biti na plećima samog modela.

Novi `Request` postaje isključivo **tanka ljuska i facade (state owner)** za PSR-7 operacije.

- Za skladištenje i preračunavanje header-a brinuće isključivo `RequestHeaders`.
- Za tumačenje body-ja sa parserima brinuće `ParseBodyByContentType` prosleđeno ka `ParsedBody`.
- Bilo koja transformacija koja nastaje iz `Request::withHeader(x, y)` vraćaće sigurni novi `Request` klon čijem će se
  objektu delegirati novo `RequestHeaders::put()` stanje, čime obezbeđujemo immutability bez trait haosa.

Security rules, poput *Bearer Token parsiranja* ili *Client IP rešavanja*, apsolutno gube pravo da žive na PSR surface-u
i selimo ih u isključive action holdere (`ResolveClientAddress`, `BearerTokenReader`).

## Posledice (Consequences)

Ova arhitektura omogućava da request može istovremeno biti lagan za održavanje (TDD per slice/capability) i formalno
prihvaćen u middlewares drugih sistema (kompatibilan interop). Nedostatak je blago veći broj namenskih instanci, no
memorijski/peformance tradeoff je zanemarljiv spram dobitka u čistoj arhitekturi (maintainability and testing quality is
drastically improved).
