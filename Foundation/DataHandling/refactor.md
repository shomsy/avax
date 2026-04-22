## Hard Rule: how-to documents are the binding source of truth

The AI MUST treat every rule defined in the `AI Prompts` folder, especially all `how-to-*.md` documents, as a mandatory implementation and review contract.

These documents are not guidance, inspiration, or optional style notes.
They are the authoritative source of truth for:
- architecture
- naming
- refactoring
- clean code
- code review
- documentation
- testing
- safety of change
- removal of obsolete structures

If any generated code, refactor, folder structure, naming choice, review note, or documentation output conflicts with a rule from the `how-to-*.md` documents, the AI MUST consider its own output wrong and MUST revise it until it fully complies.

The AI MUST NOT:
- override these rules with personal preference
- weaken them into suggestions
- ignore them for convenience
- selectively apply only the rules it likes
- preserve old structure if that structure conflicts with the how-to rules

When multiple how-to documents apply, the AI MUST reconcile them into the strictest valid result that improves clarity, ownership, simplicity, and change safety without introducing fake abstraction or architecture theater.

Compliance with the `how-to-*.md` documents is mandatory.
Any output that does not follow them is incomplete.




Evo **širokog, detaljnog i striktno how-to vođenog plana** za refaktor `DataHandling` u novu `DataModeling` strukturu. Osnova plana je sledeća:

`DataHandling` kao ime je preširoko i slabo govori ownership. Tvoja pravila traže da folder kaže flow ili capability, da public surface ostane mali i stabilan, da se izbegnu generic bucket-i, da refaktor ide kroz characterization testove i male bezbedne korake, i da se posle migracije aktivno brišu zastarele strukture. Trenutni snapshot pokazuje upravo problem koji taj governance gađa: `Arrhae` je prevelik public owner sastavljen od mnogo trait-ova, a odgovornosti su razlivenе kroz `ArrayHandling`, `Traits` i srodne delove.     

Zato je ispravan cilj refaktora ovaj 🙂
Ne “ulepšavanje foldera”, nego **promena ose sistema**:

* `DataHandling` -> `DataModeling`
* `Arrhae` ostaje root public facade za raw array API
* `Collection` postaje root public object API za fluent chain rad
* deljena logika ide u internal operation layer
* `DTO` ide kao zasebna capability zona, ali u drugoj fazi
* dokumentacija mora da prati ownership foldere kroz `how-this-works.md`, a zatim i kroz `docs/` mirror tree.   

Ispod je plan koji bih ja zaključao kao finalan radni plan.

## 1. Zaključane arhitektonske odluke

1. `Arrhae` i `Collection` su **zasebni root public surface unit-i**.
   `Arrhae` **ne extends** `Collection`. `Collection` **ne extends** `Arrhae`. Deljenje ide kroz composition i internal operations, ne kroz inheritance theater. To direktno prati pravila o small public surface, composition over inheritance, honest ownership i zabrani dumping-ground root klasa.  

2. `Arrhae` i `Collection` smeju da dele veliki deo vocabulary-ja, ali ne smeju da budu dva ista owner-a pod dva imena. `Arrhae` je raw-array entry surface. `Collection` je state owner i fluent wrapper. Pravila izričito brane duplicate owners i giant god classes.  

3. Refaktor ide **fazno**, uz characterization testove pre migracije. Big-bang haos je zabranjen. Refaktor mora da čuva behavior dok se ne odluči da se behavior namerno pooštri.  

4. `DTO` capability ide u **fazi 2**, ne u fazi 1. Prvo stabilizuješ `Arrhae`, `Collection` i `Collections/*`, pa tek onda hydration/validation/serialization/mapping. To je u skladu sa local truth before shared abstraction i staged rewrite pravilom.  

5. Svaki ownership folder mora da ima `how-this-works.md`, a kompletna dokumentacija mora da živi pod `docs/` i mirror-uje source tree. Prazni lažni stubovi nisu dovoljno dobri. Minimalni docs moraju biti stvarni i upotrebljivi.   

---

## 2. Krajnji cilj refaktora

Krajnji cilj nije da “prepakujemo trait-ove”, nego da sistem počne da se čita ovim redosledom:

`DataModeling -> public surface -> capability -> operation -> function`

To je direktno u skladu sa tvojim pravilima: folder kaže capability, unit kaže responsibility, function kaže exact action. Takođe, root sme da sadrži mali broj stabilnih public surface jedinica ako su jasne, male i odvojene od interne mašinerije.  

---

## 3. Finalni target project tree

Ovo bih postavio kao **krajnje ciljno stanje**. Nije sve za implementaciju prvog dana, ali je ovo shape koji treba da vodi ceo refaktor.

```text
Project/
  src/
    DataModeling/
      how-this-works.md

      Arrhae.php
      Collection.php
      CollectionInterface.php

      Collections/
        how-this-works.md

        Internal/
          how-this-works.md

          SharedArrayOperations.php
          SharedCollectionOperations.php
          OperationResult.php
          CollectionState.php
          CollectionMutationGuard.php
          CollectionItems.php
          DotPath.php
          NormalizedIterable.php
          JsonEncoding.php
          JsonDecoding.php
          SearchThreshold.php

        Create/
          how-this-works.md
          CreateCollection.php
          WrapIntoCollection.php
          CreateImmutableCollection.php
          CreateCollectionFromJson.php

        Read/
          how-this-works.md
          ReadValue.php
          ReadValueByPath.php
          HasValue.php
          ReadFirstValue.php
          ReadLastValue.php
          ReadKeys.php
          ReadValues.php
          PluckValues.php
          ReadOnlyKeys.php
          ReadExceptKeys.php

        Write/
          how-this-works.md
          PutValue.php
          PutValueByPath.php
          AppendValue.php
          PrependValue.php
          ForgetValue.php
          PullValue.php
          MergeValues.php
          ReplaceValues.php
          LockCollection.php
          AssertCollectionIsMutable.php

        Transform/
          how-this-works.md
          MapValues.php
          FilterValues.php
          RejectValues.php
          ReduceValues.php
          EachValue.php
          TapCollection.php
          PipeCollection.php
          FlattenValues.php
          CollapseValues.php
          FlipValues.php
          UniqueValues.php
          PartitionValues.php
          ChunkValues.php
          GroupValues.php

        Aggregate/
          how-this-works.md
          CountValues.php
          SumValues.php
          AverageValues.php
          FindMinValue.php
          FindMaxValue.php

        Search/
          how-this-works.md
          ContainsValue.php
          SearchValue.php
          MatchTextPartially.php
          MatchTextBySimilarity.php
          MatchTextFuzzily.php
          MatchTextByLevenshtein.php
          MatchTextBySortedTokens.php
          MatchTextPhonetically.php
          MatchTextByPattern.php

        Order/
          how-this-works.md
          SortValues.php
          SortValuesBy.php
          ReverseValues.php
          ShuffleValues.php

        Convert/
          how-this-works.md
          ConvertCollectionToArray.php
          ConvertCollectionToJson.php
          ConvertCollectionToXml.php
          ExpandDotKeys.php
          FlattenIntoDotKeys.php
          ConvertIterableToArray.php

        Strings/
          how-this-works.md
          JoinValues.php
          UppercaseValues.php
          LowercaseValues.php
          TitleCaseValues.php
          TrimValues.php
          ConvertValuesToCamelCase.php

      DTO/
        how-this-works.md

        DTO.php
        DTOInterface.php
        DTOCollection.php
        DTOCollectionInterface.php

        Hydration/
          how-this-works.md
          HydrateDto.php
          HydrateDtoCollection.php
          ReadPropertyMetadata.php
          CachePropertyMetadata.php
          CastPropertyValue.php
          AssignPropertyValue.php
          HandleMissingProperty.php

        Validation/
          how-this-works.md
          ValidateDto.php
          ValidateDtoCollection.php
          RuleValidator.php

          Attributes/
            how-this-works.md
            Hidden.php
            AbstractRule.php

          Rules/
            how-this-works.md

            Scalars/
              how-this-works.md
              ValidateInteger.php
              ValidateNumeric.php
              ValidateBoolean.php

            Text/
              how-this-works.md
              ValidateEmail.php
              ValidateUuid.php
              ValidateMacAddress.php
              ValidateJsonString.php

            Dates/
              how-this-works.md
              ValidateDate.php

            Enums/
              how-this-works.md
              ValidateEnumValue.php
              ValidateEnumArray.php

            Arrays/
              how-this-works.md
              ValidateDtoArray.php

            Decisions/
              how-this-works.md
              ValidateAccepted.php
              ValidateAcceptedIf.php

        Serialization/
          how-this-works.md
          ConvertDtoToArray.php
          ConvertDtoToJson.php
          SkipHiddenProperties.php

      Mapping/
        how-this-works.md
        MapArrayIntoDto.php
        MapArrayIntoDtoCollection.php
        MapDtoIntoArray.php

      Foundation/
        how-this-works.md

        Exceptions/
          how-this-works.md
          DataModelingException.php
          CollectionMutationException.php
          InvalidCollectionPathException.php
          InvalidSearchThresholdException.php
          CollectionEncodingException.php
          DtoHydrationException.php
          DtoValidationException.php
          MappingException.php

  tests/
    DataModeling/
      Arrhae/
        ArrhaeCharacterizationTest.php
        ArrhaeReadApiTest.php
        ArrhaeWriteApiTest.php
        ArrhaeTransformApiTest.php
        ArrhaeSearchApiTest.php
        ArrhaeConvertApiTest.php

      Collection/
        CollectionCharacterizationTest.php
        CollectionReadApiTest.php
        CollectionWriteApiTest.php
        CollectionTransformApiTest.php
        CollectionAggregateApiTest.php
        CollectionSearchApiTest.php
        CollectionConvertApiTest.php

      Collections/
        Read/
          ReadValueTest.php
          ReadValueByPathTest.php
          HasValueTest.php
          PluckValuesTest.php

        Write/
          PutValueTest.php
          PutValueByPathTest.php
          PullValueTest.php
          LockCollectionTest.php

        Transform/
          MapValuesTest.php
          FilterValuesTest.php
          ReduceValuesTest.php
          ChunkValuesTest.php
          GroupValuesTest.php

        Aggregate/
          SumValuesTest.php
          AverageValuesTest.php
          FindMinValueTest.php
          FindMaxValueTest.php

        Search/
          MatchTextPartiallyTest.php
          MatchTextBySimilarityTest.php
          MatchTextFuzzilyTest.php
          MatchTextByLevenshteinTest.php

        Order/
          SortValuesTest.php
          SortValuesByTest.php
          ReverseValuesTest.php

        Convert/
          ConvertCollectionToArrayTest.php
          ConvertCollectionToJsonTest.php
          FlattenIntoDotKeysTest.php
          ExpandDotKeysTest.php

      DTO/
        Hydration/
        Validation/
        Serialization/

      Mapping/
        MapArrayIntoDtoTest.php
        MapArrayIntoDtoCollectionTest.php
        MapDtoIntoArrayTest.php

  docs/
    DataModeling/
      how-this-works.md
      Arrhae.md
      Collection.md
      CollectionInterface.md

      Collections/
        how-this-works.md
        Internal/
          how-this-works.md
        Create/
          how-this-works.md
        Read/
          how-this-works.md
        Write/
          how-this-works.md
        Transform/
          how-this-works.md
        Aggregate/
          how-this-works.md
        Search/
          how-this-works.md
        Order/
          how-this-works.md
        Convert/
          how-this-works.md
        Strings/
          how-this-works.md

      DTO/
        how-this-works.md
        DTO.md
        DTOInterface.md
        DTOCollection.md
        DTOCollectionInterface.md
        Hydration/
          how-this-works.md
        Validation/
          how-this-works.md
          Attributes/
            how-this-works.md
          Rules/
            how-this-works.md
            Scalars/
              how-this-works.md
            Text/
              how-this-works.md
            Dates/
              how-this-works.md
            Enums/
              how-this-works.md
            Arrays/
              how-this-works.md
            Decisions/
              how-this-works.md
        Serialization/
          how-this-works.md

      Mapping/
        how-this-works.md

      Foundation/
        how-this-works.md
        Exceptions/
          how-this-works.md
```

Ovaj tree poštuje nekoliko bitnih stvari odjednom. Root public surface ostaje mali. Capability ownership je jasan. Internal machinery je odvojena. Subfolderi postoje samo tamo gde smanjuju šum i čuvaju ownership. Documentation tree mirror-uje source tree. To je direktno u skladu sa architecture, clean code i documentation pravilima koja si uploadovao.    

---

## 4. Šta tačno ostaje u root-u

### `Arrhae.php`

Ovo je Laravel-style raw array facade. Treba da bude mali, stabilan i eksplicitan root entry. Sme da izloži ergonomiju nad sirovim nizovima, ali ne sme da bude gomila trait-ova i sakrivenih owner-a kao danas. Trenutni snapshot već pokazuje da je `Arrhae` postao preširok, sa agregacijom, konverzijom, laziness, locking, macros, partitioning, sort, string manipulation i advanced search mogućnostima, što ga približava junk-drawer owner-u.  

U `Arrhae` bih ostavio samo:

* named constructors i ergonomic entry
* helper-style raw array operations
* bridge u `Collection`, npr. `collect()` ili `wrap()`
* delegaciju ka internim operation owner-ima

Primer surface operacija:

* `get`
* `has`
* `set`
* `forget`
* `pull`
* `only`
* `except`
* `pluck`
* `wrap`
* `collect`
* `dot`
* `undot`
* `toArray`
* `toJson`

### `Collection.php`

Ovo je state owner za fluent, chainable, object-style API. On sme da bude bogatiji od `Arrhae`, ali i dalje ne sme da bude dumping ground. Njegov posao je da drži kolekcijsko stanje i fluent lifecycle, dok prava operativna logika i dalje treba da ostane po capability owner-ima ispod `Collections/*`. To prati pravilo “small public surface, internal detail hidden, honest ownership”.  

U `Collection` bih držao surface kao:

* `map`
* `filter`
* `reduce`
* `groupBy`
* `chunk`
* `sort`
* `sortBy`
* `values`
* `keys`
* `first`
* `last`
* `push`
* `prepend`
* `merge`
* `partition`
* `pipe`
* `tap`
* `toArray`
* `toJson`

---

## 5. Šta ide u `Collections/Internal/`

Ovo je važan deo. Danas je shared logika razlivena kroz trait-ove, što povećava skrivenost i ownership blur. Trenutni `AbstractDependenciesTrait`, `DebugTrait`, `SetOperationsTrait` i slični delovi već pokazuju da veliki trait-sprawl tera behavior u skrivena mesta i da surface owner nosi previše nevidljivog tereta.   

U `Internal/` treba da ode:

* normalizacija ulaza
* state/value holder-i
* reusable policy objects
* guard objekti
* threshold/value objects za search
* dot path parsing
* JSON encode/decode helpers
* internal glue koji ne treba da bude public API

Ovo nije “helper drawer”, nego **skriveni runtime machinery** iza malog public surface-a. Pravila dopuštaju interno odvajanje mašinerije, ali zabranjuju da root public unit postane kontejner za sve.  

---

## 6. Faze refaktora

### Faza 0. Zamrzavanje istine

Pre bilo kakvog rename-a ili extraction-a, prvo moraš da zaključaš postojeće ponašanje kroz characterization testove. Refaktor bez toga je kockanje. Tvoja pravila su vrlo eksplicitna: za refaktor prvo pišeš characterization testove za postojeće ponašanje, tek onda menjaš strukturu.  

Rezultat faze:

* lista svih javnih metoda koje danas `Arrhae` izlaže
* lista edge-case ponašanja
* lista implicitnih failure mode-ova
* testovi koji štite današnje ponašanje

### Faza 1. Rename i public surface split

Ovde praviš novi root `DataModeling/`, uvodiš `Arrhae.php`, `Collection.php`, `CollectionInterface.php`, i `Collections/*` capability zonu. `DataHandling` još ne brišeš odmah, nego uvodiš controlled migration window. Pravilo je staged rewrite, ne big-bang. 

Rezultat faze:

* novi namespace skeleton postoji
* novi root public unit-i postoje
* novi capability folderi postoje
* stari kod još radi

### Faza 2. Internal extraction

Ovde razbijaš stari `Arrhae` i trait-ekosistem na operation owner-e po capability zonama. Ne radiš to sve odjednom. Radiš po grupama:

* read
* write
* transform
* aggregate
* search
* order
* convert
* strings

Svaka grupa prolazi kroz testove, pa migraciju, pa cleanup. To je direktno u skladu sa incremental TDD i small safe steps pravilima.  

### Faza 3. Root API cleanup

Kada operations prorade, svodiš `Arrhae` i `Collection` na thin public surfaces. Sve što je suvišan public teret ide dole ili se briše. Ovo je ključni trenutak da `Arrhae` prestane da bude god class. Pravila to praktično zahtevaju. 

### Faza 4. DTO capability

Tek sada ulaziš u `DTO/`, `Hydration/`, `Validation/`, `Serialization/`, `Mapping/`. Ovo je druga priča i ne treba je mešati sa collections rescue-om u istoj eksploziji. 

### Faza 5. Documentation pass

Dodaješ `how-this-works.md` u svaki ownership folder. Nakon toga gradiš `docs/` mirror tree. Dokumentacija nije bonus, nego deo design accountability ugovora.  

### Faza 6. Legacy deletion

Na kraju brišeš:

* zastarele trait-ove
* deprecated sinonime
* duplicate owners
* compatibility layer koji je odradio svoje
* stari `DataHandling/*` put, kada se prozor migracije zatvori

Ovo je izričito traženo u removal pravilima. Nemoj da ostavljaš mrtve obrasce “za svaki slučaj”. 

---

## 7. Operativni plan rada po sprintovima

### Sprint 1. Inventory i characterization

Cilj:

* napraviti tačan inventory javnog API-ja starog `Arrhae`
* grupisati metode po capability kategorijama
* dodati characterization testove za sve kritične metode

Izlaz:

* `ArrhaeCharacterizationTest.php`
* testovi za read/write/transform/search/convert grupu
* dokument `old-api-inventory.md` ili interni radni spisak

### Sprint 2. Novi skeleton

Cilj:

* kreirati `src/DataModeling/`
* kreirati `Arrhae.php`, `Collection.php`, `CollectionInterface.php`
* kreirati `Collections/*` capability foldere i `Internal/`
* kreirati minimalne `how-this-works.md` fajlove

Izlaz:

* novi tree postoji
* još nema potpunog premeštanja logike
* namespaces i autoload osnovno rade

### Sprint 3. Read + Write migracija

Cilj:

* prvo izvući operacije koje najmanje eksplodiraju API i najlakše se verifikuju:

  * `get`
  * `has`
  * `set`
  * `forget`
  * `pull`
  * `first`
  * `last`
  * `pluck`

Izlaz:

* `Read/*` i `Write/*` rade
* `Arrhae` delegira
* `Collection` delegira
* stari trait-ovi za te operacije više nisu owner-i

### Sprint 4. Transform + Aggregate

Cilj:

* migrirati:

  * `map`
  * `filter`
  * `reduce`
  * `chunk`
  * `groupBy`
  * `sum`
  * `average`
  * `min`
  * `max`
  * `count`

Izlaz:

* transform i aggregate capability su jasni
* chain API na `Collection` radi
* raw API na `Arrhae` radi

### Sprint 5. Search + Order + Convert

Cilj:

* migrirati string and search mogućnosti
* migrirati sort/reverse/shuffle
* migrirati `toArray`, `toJson`, `fromJson`, `dot`, `undot`

Ovde posebno obrati pažnju na search threshold behavior i failure modes, pošto trenutni kod već ima fuzzy/similarity/levenshtein varijante i validaciju praga. 

### Sprint 6. Root cleanup

Cilj:

* svesti `Arrhae` i `Collection` na stabilne public surface-ove
* obrisati obsolete trait-ove
* ukloniti duplicate owners
* pooštriti contracts gde je moguće bez BC udara

### Sprint 7. DTO phase

Cilj:

* kreirati `DTO/*`
* premestiti hydration/validation/serialization/mapping
* odvojiti DTO capability od collection capability

### Sprint 8. Final documentation and deletion

Cilj:

* završiti `docs/` mirror
* ukloniti legacy namespace putanje
* zatvoriti migration window

---

## 8. Konkretniji ToDo

Ispod je **radni ToDo** koji bih dao AI-u ili timu.

### A. Discovery i zaštita ponašanja

* [ ] Napraviti kompletan inventory trenutnog `Arrhae` public API-ja.
* [ ] Grupisati sve postojeće metode po capability kategorijama.
* [ ] Obeležiti koje metode pripadaju `Arrhae`, koje `Collection`, a koje treba da budu internal.
* [ ] Dodati characterization testove za sve javne metode koje ostaju podržane.
* [ ] Dodati regression testove za dot path behavior.
* [ ] Dodati regression testove za locking/mutability behavior.
* [ ] Dodati regression testove za JSON encode/decode behavior.
* [ ] Dodati regression testove za fuzzy/similarity/levenshtein behavior.
* [ ] Popisati sve implicitne exception/failure slučajeve.

### B. Nova struktura

* [ ] Kreirati `src/DataModeling/`.
* [ ] Kreirati root public surface fajlove:

  * [ ] `Arrhae.php`
  * [ ] `Collection.php`
  * [ ] `CollectionInterface.php`
* [ ] Kreirati `Collections/` capability zonu.
* [ ] Kreirati `Collections/Internal/`.
* [ ] Kreirati capability foldere `Create`, `Read`, `Write`, `Transform`, `Aggregate`, `Search`, `Order`, `Convert`, `Strings`.
* [ ] Kreirati `Foundation/Exceptions/`.

### C. Arrhae refaktor

* [ ] Svesti `Arrhae` na thin facade.
* [ ] Ukloniti direktno ownership gomile trait-ova iz `Arrhae`.
* [ ] Uvesti delegaciju iz `Arrhae` ka operation owner-ima.
* [ ] Ostaviti `Arrhae::collect()` ili ekvivalentni bridge u `Collection`.
* [ ] Ostaviti raw-array ergonomiju kao jasan razlog za postojanje `Arrhae`.

### D. Collection refaktor

* [ ] Implementirati `Collection` kao state owner.
* [ ] Implementirati `IteratorAggregate`, `Countable` i eventualne nužne ugovore ako ostaju smisleni.
* [ ] Obezbediti fluent API bez curenja internog state owner haosa.
* [ ] Uvesti controlled mutability policy.
* [ ] Odvojiti create/wrap/immutable entry semantiku.

### E. Trait rescue

* [ ] Popisati sve postojeće trait-ove iz starog `ArrayHandling/Traits`.
* [ ] Svaki trait mapirati na novi capability folder.
* [ ] Zabraniti zadržavanje “just move the trait” pristupa.
* [ ] Svaki trait razbiti na named operation owner-e ako nosi više od jedne poštene odgovornosti.
* [ ] Ostaviti trait samo ako stvarno predstavlja zdrav lokalni mehanizam, ne glavni owner.

### F. Search capability

* [ ] Izvući threshold validaciju u jasan owner, npr. `SearchThreshold`.
* [ ] Odvojiti partial, similarity, fuzzy, levenshtein, sorted-token search na posebne unit-e.
* [ ] Dodati failure mode testove za nevažeće pragove i loše inpute.

### G. Convert capability

* [ ] Odvojiti encode/decode logiku.
* [ ] Razdvojiti `toArray`, `toJson`, `toXml`, `dot`, `undot`.
* [ ] Uvesti jasne exception tipove za encoding/decoding greške.

### H. Documentation

* [ ] Dodati `how-this-works.md` u svaki ownership folder odmah.
* [ ] Koristiti stvarne file/function reference, ne generičke placeholdere.
* [ ] Dodati `docs/DataModeling/` mirror tree.
* [ ] Za svaki root public file dodati `.md` opis u `docs/`.
* [ ] Dokumentovati realne handoff-e, ulaze, izlaze, debug-first tačke i failure mode-ove.

### I. Legacy cleanup

* [ ] Uvesti migration window za stari namespace.
* [ ] Označiti legacy sinonime kao deprecated.
* [ ] Nakon migracije ukloniti obsolete layers i duplicate owners.
* [ ] Ukloniti mrtve trait-ove i namespace-ove.
* [ ] Ukloniti `DataHandling/` kada zamena postane potpuna.

---

## 9. Šta ne smeš da uradiš

Ovo je jednako važno kao i sam plan.

* Nemoj samo preimenovati `DataHandling` u `DataModeling` i ostaviti isti haos unutra. To bi bilo kozmetičko pomeranje problema. 
* Nemoj praviti `Collection extends Arrhae` ili `Arrhae extends Collection` samo da bi “delili moći”. To je convenience inheritance, a tvoja pravila guraju composition i jasnoću. 
* Nemoj zadržati stari trait-sprawl kao novi internal layer bez promene ownership-a. To bi bilo sakrivanje haosa, ne refaktor.
* Nemoj raditi `Collections`, `DTO`, `Mapping`, docs i total cleanup u jednom potezu. Pravila eksplicitno zabranjuju big-bang haos. 
* Nemoj ostavljati obsolete sinonime zauvek. Temporary adapter je dozvoljen, ali mora imati migracioni rok.  

---

## 10. Minimalna isporuka prve faze

Ako želiš **najuži, ali zdrav** prvi delivery, onda je to ovo:

```text
src/
  DataModeling/
    how-this-works.md
    Arrhae.php
    Collection.php
    CollectionInterface.php

    Collections/
      how-this-works.md

      Internal/
        how-this-works.md
        CollectionState.php
        CollectionItems.php
        DotPath.php
        CollectionMutationGuard.php

      Read/
        how-this-works.md
        ReadValue.php
        ReadValueByPath.php
        HasValue.php
        ReadFirstValue.php
        ReadLastValue.php
        PluckValues.php

      Write/
        how-this-works.md
        PutValue.php
        PutValueByPath.php
        AppendValue.php
        ForgetValue.php
        PullValue.php

      Transform/
        how-this-works.md
        MapValues.php
        FilterValues.php
        ReduceValues.php
        ChunkValues.php
        GroupValues.php

      Convert/
        how-this-works.md
        ConvertCollectionToArray.php
        ConvertCollectionToJson.php
        FlattenIntoDotKeys.php
        ExpandDotKeys.php

    Foundation/
      how-this-works.md
      Exceptions/
        how-this-works.md
        DataModelingException.php
        CollectionMutationException.php
        InvalidCollectionPathException.php
```

Ako prvo isporučiš ovo, dobijaš:

* novi root shape
* jasan split `Arrhae` vs `Collection`
* najkritičnije capability zone
* testabilnu migracionu bazu
* daleko manji rizik od raspada.  

---

## 11. Konačna preporuka

Moj hladan, praktičan savet je:

Prvo uradi **Collections rescue**, ne ceo univerzum.
Zaključaj behavior testovima.
Razbij stari `Arrhae` na poštene capability owner-e.
Svedi `Arrhae` i `Collection` na male public surface-e.
Tek onda uvodi `DTO`, `Mapping` i full docs mirror.

To je najmanje glamurozan put, ali je najzdraviji. A po tvojim pravilima, upravo je to poenta. Boring, jasno, evolutivno, bez pattern-teatra.   