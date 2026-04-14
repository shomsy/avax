According to a document from April 14, 2026, tvoj **10/10 TODO** je sada dosta kraći nego ranije. Najnoviji status kaže da su **auth kernel hardening, tenant/control-plane jezgro, SCIM runtime core, OAuth sender-constrained core, lifecycle hardening, quality gates, glossary, session revocation SQL+Redis i deployment trust docs/checklist** već urađeni, dok su **source-of-truth cleanup, OIDC provider completeness, deployment trust smoke execution, trusted-device / support explainability** još samo delimično zatvoreni, a **external certification program** i dalje nije implementiran. U kodu se i dalje vidi i jedan bitan detalj: `ReadActiveSessions` prima `SessionRegistryInterface|null` i vraća praznu listu kada registry nije wired, što znači da session story još nije “fail-closed by default” u svakom deploymentu.

## P0 — obavezno za 10/10

* [ ] **Zaključaj session registry kao first-class runtime requirement**

    * U enterprise modu nemoj dozvoliti nullable `SessionRegistryInterface`.
    * Dodaj boot-time fail-fast ako nema durable registry-ja.
    * Standardizuj i dokaži SQL i Redis adaptere kao kanonske backend-e.
    * Pokrij testovima: `logout all`, revoke one session, revoke after password change, revoke after MFA recovery, revoke after admin lock.
    * Ukloni mogućnost da “active sessions” vrati prazno samo zato što registry nije wired.

* [ ] **Završi source-of-truth cleanup**

    * `Auth.txt` još sadrži istorijski materijal; prebaci finalnu istinu u jedan kanonski `docs/STATUS.md`.
    * Capability matrix generiši iz koda i testova gde god možeš.
    * Jedan dokument neka bude jedina istina za: shipped, partial, non-goal, deprecated.
    * Ukloni razliku između “status summary”, “risk register” i “history dump”.

## P1 — zatvori preostale rupe u provider/product sloju

* [ ] **Dovrši OIDC provider completeness**

    * Najnoviji status kaže da su `RegisterClient`, logout, pairwise, PAR/JARM uglavnom zatvoreni, ali da je **client-signed JAR verification** još boundary, ne potpuno zatvorena product capability.
    * Dodaj punu verifikaciju signed request object-a, claim validation, replay/expiry testove i interoperability test matrix.
    * Dodaj conformance testove za:

        * dynamic client registration lifecycle
        * front-channel logout
        * back-channel logout
        * pairwise subjects
        * PAR/JARM/JAR kombinacije.

* [ ] **Izvrši deployment trust smoke testove, ne samo napiši docs**

    * Proxy contract i operator checklist postoje, ali latest status i dalje kaže da smoke testovi tek treba da se izvrše.
    * Dodaj CI / integration profile za:

        * reverse proxy header trust
        * mTLS chain propagation
        * DPoP proof mismatch
        * mixed proxy + sender-constraint scenarije.

## P1 — UX / operator quality da stvarno bude 10/10

* [ ] **Integrši “why” surfaces u runtime**

    * `why access denied`
    * `why step-up required`
    * `why session revoke failed / nije propagiran`
    * `why sender-constraint failed`
    * `why trusted device nije priznat`
    * Ovo sada postoji više kao support/docs pravac nego kao pravi runtime/operator surface.

* [ ] **Zatvori trusted-device odluku**

    * Trenutno je to i dalje eksplicitna product choice, ne gotova capability.
    * Za 10/10 implementiraj:

        * device token model
        * device binding
        * per-device revocation
        * audit trail
        * re-challenge policy
    * Ako to ne želiš, onda ne claim-uj 10/10 “identity platform”, nego 10/10 “high-security auth kernel”.

## P2 — release rigor i dokazivost

* [ ] **Zatvori quality claim sa mutation/test dokazom**

    * Stariji risk register i dalje pokazuje da je mutation tooling radio, ali da kritične auth slice-ove još nisu bile dovoljno mutation-hard (`MSI 61%`, escaped mutants, timeout-i).
    * Ako je to i dalje aktuelno, digneš coverage i stabilizuješ mutation suite dok release claim ne bude samodokaziv.

* [ ] **External certification program**

    * Latest status ga i dalje vodi kao neimplementiran.
    * Dodaj conformance harness, repeatable certification environment i evidence bundle po release-u.

## P2 — polish, ne više core rupa

* [ ] **Compatibility migration zatvori do kraja**

    * Avax container namespace break je i dalje accepted compatibility risk.
    * Dodaj BC shim ili jasan major-version migration path sa automated upgrade proverom.

* [ ] **Jedan jasan “product boundary”**

    * Jasno odvoji:

        * auth kernel
        * identity kernel
        * full identity platform
    * To sprečava overclaim i olakšava release/story/pre-sales pozicioniranje. Latest status već ide u tom smeru, ali 10/10 traži da to bude kristalno jasno i automatski proverljivo.

## Moj iskren redosled

Prvo uradi:

1. session registry fail-closed
2. source-of-truth cleanup
3. client-signed JAR completeness
4. deployment smoke execution
5. trusted-device runtime ili trajno odbacivanje

Zatim:
6. explainability surfaces
7. mutation rigor
8. certification harness
9. compatibility migration cleanup.

Najkraće:
**Do 10/10 te sada više ne deli “još auth feature-a”, nego završavanje fail-closed session priče, provider completeness, deployment proof, operator explainability i release-grade dokazivost.**

