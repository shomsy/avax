Da. Evo plan kako bih ovo radio bez šminke, nego kao pravi deep refactor. Doslovce uradi po pravilima iz AI Prompts
foldera.

Osnovna pretpostavka je ova: URI komponenta danas nema jednog poštenog owner-a. Odgovornost je rasuta između `BaseUri`,
`UriBuilder`, `QueryParams`, `Protocol`, `Components/*` i trait-ova, a serializer i authority logika postoje na više
mesta. To je signal za `Redesign`, ne za sitno peglanje. Tvoja governance pravila traže da refactor prvo rekonstruiše
realan sistem, zatim popravi ownership, naming, boundary clarity i testability, i da se stari lažni owner-i posle
aktivno obrišu.

Predloženi cilj je da `URI/` ostane capability slice, ali da dobije jedan kanonski model i jednu istinu za parse, state
i render. To je u skladu sa pravilom: folder govori capability, unit govori responsibility, funkcija govori exact
action. Istovremeno, dokumentacija mora da se preseli i organizuje pod top-level `docs/`, da prati source tree, i da
svaki ownership folder dobije pravi `how-this-works.md` sa frontmatter-om, realnim trigger-ima i mermaid dijagramom.

Moj target shape bi bio ovakav:

```text
HTTP/
  URI/
    Uri.php
    ParseUriString.php
    Parts/
      Authority.php
      Fragment.php
      Host.php
      Path.php
      Port.php
      Query.php
      Scheme.php
      UserInfo.php
```

A docs ovako:

```text
docs/
  HTTP/
    URI/
      how-this-works.md
      Uri.md
      ParseUriString.md
      Parts/
        how-this-works.md
        Authority.md
        Fragment.md
        Host.md
        Path.md
        Port.md
        Query.md
        Scheme.md
        UserInfo.md
```

Zašto baš ovako? Zato što današnji `UriBuilder` realno glumi i parser i builder i mutator i renderer, dok `QueryParams`
drži mutable stanje, `Protocol` i `Scheme` dupliraju isti koncept, a string rendering postoji kroz više putanja. To
treba svesti na jedan owner `Uri`, jedan boundary parser `ParseUriString`, i male part owner-e koji čuvaju invariants.

Plan za refaktor

1. Rekonstrukcija postojećeg ponašanja

Prvo nemoj dirati strukturu. Izvuci realan “as-built” model: kako URI ulazi, gde se parsira, gde se normalizuje, gde se
menja query, gde se renderuje nazad u string, i koje su danas javne ulazne tačke. Tvoja review pravila traže upravo to
pre bilo kakvog predlaganja popravki.

Ovde su kritične tačke koje treba eksplicitno mapirati:

* `UriBuilder::createFromString()`
* `UriBuilder::fromBaseUri()`
* `UriBuilder::appendPath()`
* `UriBuilder::build()`
* `BaseUri::__toString()`
* `Psr7UriTrait::*`
* `QueryParams` mutacije i rendering
* `Components/*` validacija i normalizacija.

2. Karakterizacioni testovi pre refaktora

Pre bilo kakvog reshape-a, napiši testove koji zaključavaju današnje ponašanje koje želiš da zadržiš. Tvoja architecture
governance je ovde vrlo jasna: za refactor prvo characterization tests, pa refactor iza njih, pa tek onda tightening ako
svesno menjaš ponašanje.

Minimalni set:

* parse valid HTTP/HTTPS URI
* parse user info
* authority sa i bez porta
* default port suppression
* host normalization, uključujući IP i IDN ponašanje
* path normalization za `.`, `..`, duplicate slash scenarije
* query read/write/add/remove/replace
* fragment round-trip
* `appendPath()` sa i bez query string dela
* invalid URI / invalid scheme / invalid host
* regression za repeated query params, jer današnji `append()` može napraviti array, a `toString()` to ne renderuje
  ispravno.

3. Uvođenje novog kanonskog owner-a

Uvedi `Uri.php` kao jedini pravi public model. On treba da bude mali, tvrd, čitljiv, i da drži part objekte umesto
rasutih primitive polja. Pravilo iz clean-code i architecture dokumenata je jasno: small stable public surface, explicit
contracts, hard to misuse.

`Uri` treba da poseduje:

* `Scheme`
* `Authority`
* `Path`
* `Query`
* `Fragment`

A `Authority` neka poseduje:

* `Host`
* `Port`
* `UserInfo`

Time konačno dobijaš jednu istinu o tome ko šta poseduje.

4. Boundary parser odvojen od modela

Uvedi `ParseUriString.php` kao jedini owner za spoljašnji string input. To je trust boundary i treba da bude eksplicitno
izdvojen. Tvoja governance pravila traže da parsing i security boundary budu blizu svog pravog owner-a, a ne sakriveni
po helperima i trait-ovima.

Poenta je:

* `Uri::fromString()` sme da postoji
* ali on samo delegira na `ParseUriString`
* sva pravila parsiranja i boundary failure mode-ovi žive na jednom mestu

5. Centralizacija string rendera

Moraš imati jedno mesto koje sklapa finalni URI string. Trenutno to rade i `BaseUri::__toString()`, i
`Psr7UriTrait::getAuthority()`, i `UriBuilder::build()`, sa potencijalno različitim pravilima. To je direktan izvor
bugova i semantičke nepoštenosti.

Pravilo:

* jedan owner sklapa authority
* jedan owner sklapa finalni string
* nema paralelnih serializer putanja

6. Query mora da postane immutable owner

`QueryParams` danas drži mutable array state i menja ga in-place, dok ostatak API-ja glumi immutable stil kroz `with*` i
clone pristup. To je loša mešavina. Novi `Query.php` treba da bude immutable i da eksplicitno vraća novu instancu za:

* add
* set
* remove
* replace
* clear

Uz to, mora da ispravno podrži repeated params i pravilan render.

7. Ujedinjenje scheme/protocol istine

`Protocol` i `Components/Scheme` ne smeju više da koegzistiraju kao dva owner-a istog koncepta. Jedan mora da preživi,
drugi mora da ide. Po tvojim pravilima, kad uvedeš bolji owner, duplikati i deprecated sinonimi se brišu.

Moj izbor je:

* zadrži `Scheme`
* obriši `Protocol`
* ako ti trebaju default port pravila, drži ih u `Scheme` ili u vrlo malom internom mapiranju unutar `Authority`/
  `Scheme`

8. PSR-7 kompatibilnost kao bridge, ne kao unutrašnji haos

Ako ti treba PSR-7 surface, zadrži interoperabilnost, ali ne dozvoli da ti PSR-7 diktira unutrašnji shape. To je
eksplicitno traženo u architecture pravilima: external compatibility da, internal disorder ne.

Znači:

* spolja možeš i dalje implementirati `UriInterface`
* unutra ne drži trait-ove kao skrivene pseudo-owner-e
* napiši eksplicitne metode u `Uri` ili tanak adapter ako baš treba

9. Brisanje stare strukture čim nova prođe

Kad novi owner-i prođu testove, obriši:

* `BaseUri`
* `QueryParams`
* `Protocol`
* `Traits/`
* stari `UriBuilder` ako nema legitimnu javnu vrednost

Ako ti treba migracioni most, neka traje kratko i bude jasno deprecated. Governance je tu vrlo direktan: ne ostavljati
obsolete layers i duplicate owners.

10. Dokumentacija na kraju, ali stvarna

Na kraju uradi docs kako treba:

* `docs/HTTP/URI/how-this-works.md`
* `docs/HTTP/URI/Parts/how-this-works.md`
* frontmatter
* mermaid
* real trigger
* gde debug first
* šta ulazi, šta izlazi, šta se renderuje, šta failuje

Sadašnji `how-this-works.md` iz `URI.txt` je praktično samo beleška, ne dokument. To ne prolazi tvoje dokumentacione
gate-ove.

ToDo lista

Faza 0. Analiza i zaštita ponašanja

* [ ] Popiši sve javne entrypoint-e trenutne URI komponente
* [ ] Nacrtaj as-built execution flow za parse → mutate → render
* [ ] Definiši koje postojeće ponašanje mora ostati 100% isto
* [ ] Definiši šta je trenutno bug, a ne contract
* [ ] Popiši sistem invariants za URI komponentu, minimum 5 komada

Faza 1. Testovi

* [ ] Dodaj characterization test za `createFromString()`
* [ ] Dodaj characterization test za `fromBaseUri()`
* [ ] Dodaj characterization test za `appendPath()`
* [ ] Dodaj characterization test za authority rendering
* [ ] Dodaj characterization test za default port suppression
* [ ] Dodaj characterization test za user info rendering
* [ ] Dodaj characterization test za host normalization
* [ ] Dodaj characterization test za path normalization
* [ ] Dodaj characterization test za fragment encoding
* [ ] Dodaj characterization test za query add/remove/replace
* [ ] Dodaj regression test za repeated query params
* [ ] Dodaj invalid input testove za scheme/host/URI parse failure

Faza 2. Nova struktura

* [ ] Uvedi `Uri.php`
* [ ] Uvedi `ParseUriString.php`
* [ ] Uvedi `Parts/Authority.php`
* [ ] Uvedi `Parts/UserInfo.php`
* [ ] Uvedi `Parts/Port.php`
* [ ] Uvedi `Parts/Query.php`
* [ ] Preseli `Host`, `Path`, `Scheme` u `Parts/`
* [ ] Uvedi `Fragment.php` ako hoćeš isti nivo banalnosti i jasnoće za sve delove

Faza 3. Ownership cleanup

* [ ] Premesti authority logiku iz više mesta u `Authority`
* [ ] Premesti finalni string render u jedan owner
* [ ] Premesti query parsing/render/mutation u `Query`
* [ ] Premesti string parse u `ParseUriString`
* [ ] Ujedini default port pravila pod jednim owner-om
* [ ] Ukloni duplu scheme/protocol istinu

Faza 4. PSR-7 i javni API

* [ ] Odluči da li `Uri` direktno implementira `UriInterface`
* [ ] Ako da, implementiraj eksplicitno, bez trait magije
* [ ] Ako ne, napravi tanak adapter i zadrži interni model čist
* [ ] Zaključaj kompatibilnost testovima na javnom API nivou

Faza 5. Brisanje starog

* [ ] Obriši `BaseUri`
* [ ] Obriši `QueryParams`
* [ ] Obriši `Protocol`
* [ ] Obriši `Traits/Psr7UriTrait.php`
* [ ] Obriši ostale URI trait-ove ako su samo workaround sloj
* [ ] Obriši `UriBuilder` ili ga svedi na privremeni deprecated adapter
* [ ] Obriši duplicate serializer putanje
* [ ] Obriši deprecated sinonime po završetku migracionog prozora

Faza 6. Dokumentacija

* [ ] Napravi `docs/HTTP/URI/how-this-works.md`
* [ ] Napravi `docs/HTTP/URI/Parts/how-this-works.md`
* [ ] Dodaj frontmatter u oba
* [ ] Dodaj realan mermaid sequence diagram
* [ ] Objasni jedan tačan put: string URI ulazi → parser → parts → Uri → string output
* [ ] Dodaj “Debug first”
* [ ] Dodaj “What to remember”
* [ ] Dodaj kratke md fajlove za `Uri`, `ParseUriString`, `Authority`, `Query` i ostale part owner-e

Faza 7. Quality gates

* [ ] Prođi static analysis
* [ ] Prođi unit testove
* [ ] Prođi integration testove ako imaš PSR-7 boundary
* [ ] Prođi documentation gates
* [ ] Proveri da više ne postoji dupli owner ni za parse, ni za query, ni za render
* [ ] Proveri da je public surface manji nego pre
* [ ] Proveri da newcomer može da pročita komponentu bez mentalnog simuliranja pola sistema

Praktična napomena: nemoj ovo raditi kao jedan ogroman patch. Radi ga u malim rezovima: characterization tests, pa
`Query`, pa `Authority`, pa `Uri`, pa parser, pa migracija public API-ja, pa deletion pass, pa docs. To je najbezbedniji
put i najviše liči na tvoju governance logiku.