Evo **finalnog targeta** i **izvodljivog ToDo plana** za Response komponentu, strogo po pravilima how-to-*.md dokumenata
iz foldera AI Prompts.

Presuda ostaje ista: **⚠️ Redesign**, ne kozmeticki refactor. Trenutni prostor mesa vise odgovornosti u istoj zoni:
PSR-7 response state, stream implementation, convenience builders za JSON/XML/HTML/text, redirect, view rendering i
direktno emitovanje preko `header()` i `echo`. Uz to, `JsonResponse` uvodi poseban business-envelope model i vuce
globalni `app(...)`, sto muti ownership i siri blast radius. To je direktan sudar sa pravilom da folder govori flow ili
capability, file govori responsibility, a function exact action.

Takodje, sadasnji `how-this-works.md` za Response ne prolazi dokumentacione gate-ove. Tvoja pravila traze frontmatter,
realan trigger, realne file/function participante, mermaid dijagram, “what user sees”, “what gets written”, i “where to
debug first”. Trenutni opis je samo kratak pregled.

Moj cilj ovde je: **savrseni, idealni, enterprise-grade Response** koji je:

* mali na public surface-u
* tvrd po kontraktima
* jasan po ownership-u
* imutabilan gde treba
* eksplicitan po side effect-ima
* prosiriv bez junk drawer efekta
* dokumentovan tako da novi covek moze da isprati putanju bez otvaranja pola sistema.

Moj finalni stav je ovaj: **Response komponenta treba da ima dve glavne ose**:

1. **BuildResponse**
   pravi ispravan HTTP response

2. **EmitResponse**
   salje vec napravljen response u PHP runtime

Sve ostalo su capabilities ispod toga. `view()` nije ownership Response-a. Business JSON envelope tipa
`status/message/data` nije ownership core Response-a. To pripada Presentation/API sloju, ne Response capability-ju.

Evo **finalnog project tree-ja** koji bih ja zakucao.

```text
HTTP/
  Response/
    Response.php
    ResponseFactory.php
    ResponseEmitter.php

    Flows/
      BuildResponse/
        BuildResponse.php
        BuildEmptyResponse.php
        BuildTextResponse.php
        BuildHtmlResponse.php
        BuildJsonResponse.php
        BuildXmlResponse.php
        BuildProblemResponse.php
        BuildRedirectResponse.php
        BuildFileDownloadResponse.php
        BuildStreamResponse.php
        BuildNoContentResponse.php
        BuildNotModifiedResponse.php
        how-this-works.md

      EmitResponse/
        EmitResponse.php
        EmitResponseStatus.php
        EmitResponseHeaders.php
        EmitResponseBody.php
        how-this-works.md

    Capabilities/
      Message/
        ResponseMessage.php
        ValidateStatusCode.php
        NormalizeProtocolVersion.php
        ResolveReasonPhrase.php
        how-this-works.md

      Headers/
        ResponseHeaders.php
        NormalizeHeaderName.php
        NormalizeHeaderValues.php
        ValidateHeaderName.php
        ValidateHeaderValues.php
        ReplaceHeader.php
        AppendHeader.php
        RemoveHeader.php
        HasHeader.php
        ReadHeader.php
        ReadHeaderLine.php
        how-this-works.md

      Body/
        ResponseBody.php
        NormalizeResponseBody.php

        Json/
          EncodeJsonBody.php
          how-this-works.md

        Xml/
          EncodeXmlBody.php
          ValidateXmlElementName.php
          how-this-works.md

        Problem/
          ProblemDetails.php
          EncodeProblemDetails.php
          how-this-works.md

        how-this-works.md

      Streams/
        ResponseStreamFactory.php
        CreateTemporaryStream.php
        CreateStreamFromString.php
        OpenFileStream.php
        RewindStream.php
        how-this-works.md

      Redirects/
        ValidateRedirectTarget.php
        NormalizeRedirectStatus.php
        how-this-works.md

      Downloads/
        DetectDownloadMediaType.php
        BuildAttachmentDisposition.php
        BuildInlineDisposition.php
        how-this-works.md

      Cookies/
        ResponseCookie.php
        SetCookieHeader.php
        ExpireCookieHeader.php
        how-this-works.md

      Caching/
        CacheControl.php
        BuildCacheControlHeader.php
        Etag.php
        LastModified.php
        BuildNotModifiedHeaders.php
        how-this-works.md

    Configuration/
      RegisterResponseServices.php
      how-this-works.md
```

I parallelni docs mirror, posto tvoja dokumentaciona pravila kazu da je `docs/` jedina kanonska lokacija i da struktura
mora da mirror-uje source tree.

```text
docs/
  HTTP/
    Response/
      Response.md
      ResponseFactory.md
      ResponseEmitter.md

      Flows/
        BuildResponse/
          how-this-works.md
          BuildResponse.md
          BuildEmptyResponse.md
          BuildTextResponse.md
          BuildHtmlResponse.md
          BuildJsonResponse.md
          BuildXmlResponse.md
          BuildProblemResponse.md
          BuildRedirectResponse.md
          BuildFileDownloadResponse.md
          BuildStreamResponse.md
          BuildNoContentResponse.md
          BuildNotModifiedResponse.md

        EmitResponse/
          how-this-works.md
          EmitResponse.md
          EmitResponseStatus.md
          EmitResponseHeaders.md
          EmitResponseBody.md

      Capabilities/
        Message/
          how-this-works.md
          ResponseMessage.md
          ValidateStatusCode.md
          NormalizeProtocolVersion.md
          ResolveReasonPhrase.md

        Headers/
          how-this-works.md
          ResponseHeaders.md
          NormalizeHeaderName.md
          NormalizeHeaderValues.md
          ValidateHeaderName.md
          ValidateHeaderValues.md
          ReplaceHeader.md
          AppendHeader.md
          RemoveHeader.md
          HasHeader.md
          ReadHeader.md
          ReadHeaderLine.md

        Body/
          how-this-works.md
          ResponseBody.md
          NormalizeResponseBody.md

          Json/
            how-this-works.md
            EncodeJsonBody.md

          Xml/
            how-this-works.md
            EncodeXmlBody.md
            ValidateXmlElementName.md

          Problem/
            how-this-works.md
            ProblemDetails.md
            EncodeProblemDetails.md

        Streams/
          how-this-works.md
          ResponseStreamFactory.md
          CreateTemporaryStream.md
          CreateStreamFromString.md
          OpenFileStream.md
          RewindStream.md

        Redirects/
          how-this-works.md
          ValidateRedirectTarget.md
          NormalizeRedirectStatus.md

        Downloads/
          how-this-works.md
          DetectDownloadMediaType.md
          BuildAttachmentDisposition.md
          BuildInlineDisposition.md

        Cookies/
          how-this-works.md
          ResponseCookie.md
          SetCookieHeader.md
          ExpireCookieHeader.md

        Caching/
          how-this-works.md
          CacheControl.md
          BuildCacheControlHeader.md
          Etag.md
          LastModified.md
          BuildNotModifiedHeaders.md

      Configuration/
        how-this-works.md
        RegisterResponseServices.md
```

Sada najbitnije. **Sta je uloga svakog root file-a**:

`Response.php`
Mali public facade. Ne sme biti god class. Njegov posao je da da banalne, prediktivne entrypoint metode ka builderima.
Na primer: `empty()`, `text()`, `html()`, `json()`, `xml()`, `problem()`, `redirect()`, `download()`, `stream()`,
`noContent()`, `notModified()`.

`ResponseFactory.php`
PSR-17 style factory/public contract entry za prazne response instance i eventualno compatibility layer tokom migracije.
Mali i dosadan.

`ResponseEmitter.php`
Public entry za runtime emission. Side effect owner. Ovde ide transport prema `http_response_code()`, `header()`,
`echo/stream copy`, nikako u `ResponseMessage`. Trenutni `send()` iz response state klase mora odavde da zivi, ne iz
message owner-a.

Sada **sta izbacujem iz postojece verzije**:

* `Classes/` folder leti. To je tehnicki bucket i semanticki slab naziv.
* `Response::send()` leti iz PSR-7 message objekta i seli se u `EmitResponse/*`.
* `ResponseFactory::view()` leti iz Response komponente. Rendering nije response ownership. Trenutno je samo proksi ka
  `view(...)`, sto je jos gore jer krije boundary.
* `JsonResponse` kao `status/message/data` envelope leti iz core Response. To je API/presentation convention i mora u
  drugi slice. Trenutni `toResponse()` sa `app(ResponseFactory::class)` je skrivena zavisnost i slab boundary.
* komentarisana validacija u `withStatus()` mora nazad kao stvarna odgovornost u `ValidateStatusCode.php`. Komentar
  umesto sigurnosne provere je los signal.

Da budem precizan, **savrseni idealni Response mora da bude potpun**, ali ne sme da postane framework warehouse. Zato
bih u prvoj punoj verziji zahtevao sledece moci:

* empty response
* text response
* html response
* json response
* xml response
* RFC 7807 style problem response
* redirect response
* file download response
* stream response
* no-content response
* not-modified response
* cookies
* cache-control
* ETag
* Last-Modified
* emitter

To je dovoljno jako da bude ozbiljna komponenta, a i dalje disciplinovano. Sve preko toga proveravas po ownership-u. Ako
nesto nije shared HTTP concern, ne ulazi ovde.

Evo i **detaljnog ToDo plana**, redom kojim bih ga ja implementirao.

1. Zakucaj odluku i granice
   Napisati kratki ADR za Response:

    * primary axis: `BuildResponse`
    * secondary flow: `EmitResponse`
    * rendering nije deo Response
    * business JSON envelope nije deo core Response
    * Response component owns HTTP response creation and emission only
      Ovo direktno prati tvoj review format i zahteva jasnu sledecu akciju.

2. Uvedi characterization tests nad postojecim javnim ponasanjem
   Pre bilo kakvog rezanja:

    * `createResponse()`
    * `createTextResponse()`
    * `createHtmlResponse()`
    * `createJsonResponse()`
    * `createRedirectResponse()`
    * `response()/send()` dispatch u factory-ju
    * `withHeader/withAddedHeader/withoutHeader`
    * `withStatus/withProtocolVersion`
    * `withJson/withXml`
    * `send()` emission behavior
      Tvoja pravila su jasna da legacy rizik ide uz seams i characterization testove, ne uz hrabrost.

3. Uvedi novi skeleton bez brisanja starog API-ja
   Napraviti:

    * `Response.php`
    * `ResponseFactory.php`
    * `ResponseEmitter.php`
    * root `Flows/`, `Capabilities/`, `Configuration/`
    * svi `how-this-works.md` placeholderi sa ispravnim frontmatter-om
      Ne popunjavati fancy logiku odmah. Prvo zakucaj shape.

4. Izdvoji message state owner
   Napraviti `Capabilities/Message/ResponseMessage.php` koji poseduje:

    * status code
    * reason phrase
    * protocol version
    * headers value object
    * body abstraction
      Tu ostaje imutabilna message logika. Bez emitovanja. Bez view rendering-a. Bez “smart convenience everything”.

5. Izdvoji header capability
   Napraviti `ResponseHeaders.php` plus normalizers/validators/readers:

    * case-insensitive storage
    * preserve original semantic values
    * validate name characters
    * validate header values
    * expose `has`, `read`, `readLine`, `replace`, `append`, `remove`
      Time izbacujes header knowledge iz svega ostalog i pravis jednu autoritativnu zonu znanja.

6. Izdvoji body capability
   Napraviti `ResponseBody.php` i `NormalizeResponseBody.php`.
   Zatim odvojene encoder-e:

    * `EncodeJsonBody.php`
    * `EncodeXmlBody.php`
    * `EncodeProblemDetails.php`
      Ovo ubija miks gde response state klasa sama pravi temp stream, serializuje JSON/XML i menja headers. Trenutno je
      to sve nagurano u isti owner.

7. Izdvoji streams capability
   Napraviti:

    * `ResponseStreamFactory.php`
    * `CreateTemporaryStream.php`
    * `CreateStreamFromString.php`
    * `OpenFileStream.php`
    * `RewindStream.php`
      Ako zadrzavas sopstveni stream tip, onda neka zivi ovde. Ako prelazis na eksterni PSR-7 stream implementation,
      onda ova capability ostaje samo kao creation/wrapping zone. Bitno je da `Classes/Stream.php` vise ne postoji kao
      zalutali technical bucket.

8. Uvedi BuildResponse flow
   Napraviti orchestration file `BuildResponse.php` i banalne entry builder-e:

    * `BuildEmptyResponse`
    * `BuildTextResponse`
    * `BuildHtmlResponse`
    * `BuildJsonResponse`
    * `BuildXmlResponse`
    * `BuildProblemResponse`
    * `BuildRedirectResponse`
    * `BuildFileDownloadResponse`
    * `BuildStreamResponse`
    * `BuildNoContentResponse`
    * `BuildNotModifiedResponse`
      Svaki builder radi jednu stvar. Naziv govori tacno sta radi. Nema generickog “send(mixed $data)” kao glavnog
      mental modela. Taj dispatch moze eventualno ostati kao adapter, ne kao centralna istina.

9. Uvedi EmitResponse flow
   Napraviti:

    * `EmitResponse`
    * `EmitResponseStatus`
    * `EmitResponseHeaders`
    * `EmitResponseBody`
      Side effects moraju da budu eksplicitni i lako testabilni. Tvoje clean-code i coding-standard smernice su direktne
      po tom pitanju.

10. Uvedi redirects capability
    Napraviti:

* `ValidateRedirectTarget`
* `NormalizeRedirectStatus`
  Redirect ima boundary pravila i ne treba da bude samo jedan `if` u factory-ju. Trenutna validacija je dobra ideja, ali
  ownership je los.

11. Uvedi problem-details capability
    Napraviti `ProblemDetails` value object i `BuildProblemResponse`.
    Time dobijas ozbiljan API error surface umesto ad hoc `['error' => ...]` JSON patterna. To je bas ono “world class”
    poboljsanje koje vredi. Nije kozmetika.

12. Uvedi cookies i caching capability
    U prvoj ozbiljnoj verziji mora postojati:

* `ResponseCookie`
* `SetCookieHeader`
* `ExpireCookieHeader`
* `CacheControl`
* `BuildCacheControlHeader`
* `Etag`
* `LastModified`
* `BuildNotModifiedHeaders`
  To su shared HTTP concerns. Imaju legitiman ownership ovde.

13. Napravi tanak public facade
    `Response.php` treba da bude mali, citljiv, prediktivan API. Na primer:

* `empty()`
* `text()`
* `html()`
* `json()`
* `xml()`
* `problem()`
* `redirect()`
* `download()`
* `stream()`
* `noContent()`
* `notModified()`
* `emit()` preko `ResponseEmitter`
  Public surface mora da bude mali i tvrd. Ne dvadeset sinonima za istu stvar.

14. Napravi migration adapter layer
    Privremeno zadrzi compatibility:

* stari `response(mixed $data, int $status = 200)` delegira u nove builder-e
* stari `send(mixed $data, int $status = 200)` u factory-ju postaje deprecated wrapper
* stari `create*Response()` metodski API moze kratko da ostane kao compatibility facade
  Ali to mora imati jasan migration window i delete plan. Tvoja pravila eksplicitno traze uklanjanje obsolete patterns
  kad novi ownership stane na noge.

15. Izbaci `view()` iz Response
    Prebaciti ga u zasebni `Rendering` ili `Presentation` slice.
    Response moze da primi gotov HTML string. Ne treba da zna za template system.

16. Izbaci `JsonResponse` envelope iz core-a
    Prebaciti ga u API/Presentation sloj, na primer:

* `Presentation/Api/Responses/ApiSuccessResponse.php`
* `Presentation/Api/Responses/ApiErrorResponse.php`
* `Presentation/Api/Responses/ApiFailureResponse.php`
  Ili jos bolje, jedan `BuildApiEnvelopeResponse` u API slice-u.
  Poenta je da core Response pravi HTTP message, a API slice odlucuje o business envelope strukturi.

17. Uvedi eksplicitne validatore i invariants
    Minimalno:

* invalid status code
* invalid protocol version
* invalid header name
* invalid header value
* invalid redirect target
* invalid XML element name
* invalid file download path / inaccessible stream
* no-body rules za 204/304 gde je potrebno
  Error paths moraju biti intentional, ne usputni.

18. Test strategija
    Minimum set:

* unit testovi za svaki validator/normalizer/value object
* unit testovi za svaki builder
* integration testovi za emitter
* abuse/edge testovi za invalid headers, invalid redirect, malformed xml keys, json encode fail
* tests for immutability and clone-with behavior
* contract tests za public facade i factory
  Tvoja pravila traze behavior/contracts/invariants fokus, ne broj testova radi broja.

19. Dokumentacija po folderima
    Svaki ownership folder dobija `how-this-works.md`.
    Za Response bih obavezno uradio makar:

* `HTTP/Response/Flows/BuildResponse/how-this-works.md`
* `HTTP/Response/Flows/EmitResponse/how-this-works.md`
* `HTTP/Response/Capabilities/Headers/how-this-works.md`
* `HTTP/Response/Capabilities/Body/how-this-works.md`
* `HTTP/Response/Capabilities/Streams/how-this-works.md`
* `HTTP/Response/Configuration/how-this-works.md`
  I to ne genericke price, nego tacan trigger, tacna putanja, realni file/function names, sta se vidi, gde se debug
  krece.

20. Kill list posle migracije
    Kad novi shape prodje:

* obrisati `Classes/`
* obrisati stari `send()` sa mixed dispatch centralnom ulogom ako vise nije potreban
* obrisati `view()` iz ResponseFactory
* obrisati `JsonResponse` iz core Response komponente
* obrisati sve compatibility wrappers kojima je istekao rok
  Nema cuvanja mrtvih slojeva “za svaki slucaj”.

Evo i **non-negotiables** koje bih zakucao pre implementacije:

* public API mora ostati mali
* emission mora biti odvojena od response state-a
* rendering ne ulazi u Response
* business JSON envelope ne ulazi u core Response
* svi error path-ovi moraju biti eksplicitni
* dokumentacija mora mirror-ovati source
* svaki ownership folder mora imati `how-this-works.md`
* migration ide staged, ne big bang
* compatibility layer ima rok i delete plan.

Ako hoces brutalno kratku definiciju: **savrseni Response je mali HTTP capability sa dosadno jasnim API-jem, jakim
invariants, odvojenim emission flow-om i nula laznih ownership-a.**
