# Avax PreCommit & Tooling System Implementation

Da. Tvoj prompt je dobar po nameri, ali mu fali disciplina: jasna arhitektura, granica odgovornosti, safe-delete
pravila, report format, i commit gate semantika.

Ja bih ga preformulisao ovako:

```text
Zelim da refaktorisem i dovrsim Avax PreCommit disciplinu kao enterprise-grade sistem za automatsku proveru projekta pre svakog commita.

Kontekst:
U framework-u vec postoji zapocet namespace:

Avax\Framework\System\Capabilities\PreCommit;

Takodje postoji tooling folder sa raznim skriptama. Zelim da ovo bude uredno povezano, tako da:

1. Framework capability PreCommit bude centralni vlasnik discipline.
2. Tooling folder sadrzi konkretne izvršne skripte, adaptere i tehničke alate.
3. Avax CLI komande pozivaju PreCommit flow.
4. Git pre-commit hook poziva Avax CLI komandu.
5. PreCommit flow orkestrira sve provere, generise report, pravi ToDo listu i vraca pass/fail status.

Cilj:
Napraviti disciplinovan, chain/pipeline organizovan sistem koji moze da proverava sve sto je bitno pre commita:

- how-to-*.md pravila
- naming konvencije
- strukturu foldera
- forbidden words
- PublicSurface pravila
- legacy fajlove
- deprecated kod
- deprecated klase/metode/alias-e
- legacy alias-e
- shim-ove
- privremene compatibility slojeve
- TODO/FIXME/HACK/XXX komentare
- mrtve ili prazne foldere
- nepotrebne skripte
- neorganizovane php, sh, go, node, python i druge skripte
- arhitektonske rupe koje krše flow-first, feature-first, screaming architecture pravila
- fajlove koji postoje na pogrešnom mestu
- duplikate odgovornosti
- generisane report fajlove i status prethodnih provera

Bitno:
Nemoj praviti jedan haotičan mega-script. Napravi jasan PreCommit sistem sa flow-orijentisanom arhitekturom.

Zeljeni flow:

PreCommit
    DetectProjectDisciplineIssues
    ClassifyDetectedIssues
    BuildSafeFixPlan
    RunSafeAutoFixes
    RunManualReviewChecks
    WritePreCommitReport
    WritePreCommitTodo
    DecideCommitStatus

Proces mora da radi ovako:

1. Detection first.
2. Classification second.
3. Safe processing third.
4. Report fourth.
5. Commit decision last.

Nijedna destruktivna akcija ne sme da se izvrši bez jasne klasifikacije.

Delete pravilo:
Legacy, deprecated, shim, alias i temporary compatibility fajlovi NE SMEJU da se automatski brišu ako nije 100% sigurno da su mrtvi.

Za svaki kandidat za brisanje sistem mora da klasifikuje:

- safe_to_delete
- probably_safe_but_requires_review
- unsafe_to_delete
- keep_because_public_api
- keep_because_compatibility_contract
- keep_because_referenced

Ako je fajl referenced, public API, deo compatibility contract-a ili nije jasno mrtav, ne brisati ga. Samo ga staviti u report i ToDo.

Dry-run mora biti default.
Auto-fix sme da radi samo za trivijalne i bezbedne stvari.
Delete operacije moraju biti posebno eksplicitne i odvojene.

Predlozena arhitektura:
PreCommit capability treba da bude framework-level owner, a tooling samo execution layer.

Primer podele:

Avax\Framework\System\Capabilities\PreCommit
    PublicSurface
        PreCommit.php
        PreCommitResult.php
        PreCommitIssue.php
        PreCommitStatus.php

    Flows
        RunPreCommit
            RunPreCommit.php
            PreCommitRunRequest.php
            PreCommitRunResult.php

        DetectProjectDisciplineIssues
            DetectProjectDisciplineIssues.php

        ClassifyDetectedIssues
            ClassifyDetectedIssues.php

        BuildSafeFixPlan
            BuildSafeFixPlan.php

        RunSafeAutoFixes
            RunSafeAutoFixes.php

        WritePreCommitReport
            WritePreCommitReport.php

        WritePreCommitTodo
            WritePreCommitTodo.php

        DecideCommitStatus
            DecideCommitStatus.php

    Capabilities
        CheckHowToRules
        CheckNamingConventions
        CheckForbiddenWords
        CheckPublicSurfaceRules
        DetectLegacyCode
        DetectDeprecatedCode
        DetectLegacyAliases
        DetectShims
        DetectTodoComments
        DetectToolingScripts
        DetectDeadFiles
        DetectDeadFolders
        DetectDuplicateResponsibilities
        DetectArchitectureViolations
        RunExternalToolingScripts
        StoreDisciplineReports

    Configuration
        PreCommitConfig.php
        PreCommitChecks.php
        PreCommitSeverity.php
        PreCommitPaths.php

    Contracts
        PreCommitCheck.php
        PreCommitFix.php
        PreCommitReportWriter.php
        ExternalToolRunner.php

    Support
        FileReference.php
        IssueLocation.php
        Severity.php
        CheckName.php

Tooling folder treba da sadrzi konkretne skripte, ali ne treba da bude glavni vlasnik discipline.

Primer:

tooling/
    pre-commit/
        run-pre-commit.php
        install-git-hook.sh
        checks/
            check-how-to-rules.php
            check-naming-conventions.php
            detect-legacy-code.php
            detect-deprecated-code.php
            detect-todo-comments.php
            detect-tooling-scripts.php
        reports/
            .gitkeep

Avax CLI treba da ima komande tipa:

php avax pre-commit
php avax pre-commit --dry-run
php avax pre-commit --fix
php avax pre-commit --report
php avax pre-commit --install-hook
php avax discipline:check
php avax discipline:report

Git pre-commit hook treba da pozove:

php avax pre-commit --dry-run

ili, ako je dogovoreno:

php avax pre-commit

Report mora da se snima u stabilan folder, na primer:

Code-Review-And-ToDo/pre-commit/pre-commit-report.md
Code-Review-And-ToDo/pre-commit/pre-commit-todo.md
Code-Review-And-ToDo/pre-commit/pre-commit-status.json

Report mora da sadrzi:

- datum i vreme provere
- pass/fail status
- listu pokrenutih checkova
- listu preskočenih checkova
- critical issues
- warnings
- info findings
- safe auto-fixes
- manual review candidates
- delete candidates
- files that must not be deleted
- TODO/FIXME/HACK komentare
- how-to rule violations
- naming violations
- legacy/deprecated/shim/alias detekcije
- preporučene sledeće korake
- checklist sa statusima

ToDo fajl mora da bude checkbox format:

- [ ] Fix naming violation in ...
- [ ] Review legacy alias ...
- [ ] Remove deprecated shim only after confirming references ...
- [x] Auto-fixed ...

Commit status pravila:

Commit mora da bude blokiran ako postoje:
- critical architecture violations
- broken how-to-*.md rules
- forbidden naming violations
- unsafe public API changes
- unresolved destructive changes
- failed tooling script
- invalid PreCommit configuration

Commit sme da prodje sa warning statusom ako postoje:
- TODO komentari koji nisu kritični
- legacy code koji je samo detektovan
- deprecated code koji nije u touched files
- manual review suggestions
- non-blocking cleanup candidates

Posebno pravilo za touched files:
PreCommit mora da razlikuje:
1. ceo projekat
2. samo fajlove promenjene u trenutnom commitu

Za pre-commit hook default treba da proverava touched files plus globalne kritične discipline.
Za ručni discipline report moze da proverava ceo projekat.

Obavezno koristi postojeći kod gde god ima smisla.
Nemoj brisati postojeće tooling skripte dok ne mapiraš šta rade.
Prvo napravi inventory postojećih tooling skripti:
- naziv
- jezik
- svrha
- ulaz
- izlaz
- da li se koristi
- da li se duplira sa drugim skriptama
- da li treba da postane PreCommit check
- da li treba da ostane standalone tool
- da li treba da bude deprecated

Za sve skripte bilo kog tipa, php, sh, go, node, python, napravi registry ili manifest koji zna:
- kako se skripta pokreće
- šta proverava
- koji exit code znači pass/fail
- gde piše output
- da li je blocking ili non-blocking
- da li sme da se pokrene u pre-commit hook-u
- da li je spora i treba da ide samo u full discipline check

Napravi clean architecture granicu:

PreCommit flow ne sme direktno da zna detalje svake shell/node/python/go skripte.
PreCommit treba da koristi ExternalToolRunner ili ScriptRunner adapter.

Naming mora da bude jednostavan, banalan, prediktivan i u skladu sa how-to-*.md pravilima.
Izbegavati generičke nazive tipa Manager, Helper, Utils, Processor ako nisu zaista opravdani.
Folder mora da kaže flow ili capability.
File mora da kaže odgovornost.
Function mora da kaže tačnu akciju.

Implementaciju radi fazno:

Phase 1:
- Procitaj postojeći PreCommit namespace.
- Procitaj tooling folder.
- Napravi inventory postojećih skripti.
- Napiši report trenutnog stanja.
- Ne briši ništa.

Phase 2:
- Predloži novu strukturu.
- Premesti samo ono što je sigurno.
- Uvedi PreCommit flow.
- Uvedi check contract.
- Uvedi result model.
- Uvedi report writer.
- Uvedi ToDo writer.

Phase 3:
- Poveži postojeće tooling skripte kroz adapter.
- Dodaj Avax CLI komandu.
- Dodaj git hook installer.
- Dodaj dry-run mode.

Phase 4:
- Dodaj checkove:
    - how-to rules check
    - naming check
    - forbidden words check
    - TODO/FIXME/HACK detector
    - legacy detector
    - deprecated detector
    - shim detector
    - alias detector
    - PublicSurface check
    - folder structure check
    - duplicate responsibility check

Phase 5:
- Dodaj safe auto-fix samo za bezbedne slučajeve.
- Dodaj delete candidate report, ali ne automatsko brisanje.
- Dodaj full report i status json.

Phase 6:
- Dodaj testove.
- Testovi moraju pokriti happy path i failure path.
- Testirati:
    - check passes
    - check fails
    - warning ne blokira commit
    - critical issue blokira commit
    - dry-run ne menja fajlove
    - auto-fix menja samo dozvoljene fajlove
    - delete candidate se ne briše bez eksplicitne dozvole
    - report se generiše
    - ToDo se generiše
    - touched files mode radi
    - full project mode radi
    - external tooling script failure se pravilno mapira

Phase 7:
- Dokumentuj:
    - kako se pokreće
    - kako se instalira hook
    - kako se dodaje novi check
    - kako se dodaje external script
    - kako se tumači report
    - šta blokira commit
    - šta je warning
    - šta je manual review
    - šta je safe auto-fix

Na kraju mi vrati:
1. Analizu postojećeg stanja.
2. Predloženu finalnu arhitekturu.
3. Listu fajlova koje si dodao.
4. Listu fajlova koje si promenio.
5. Listu fajlova koje predlažeš za brisanje, ali ih nisi obrisao ako nije 100% sigurno.
6. Komande koje treba da pokrenem.
7. Test rezultate.
8. Finalni report da li je PreCommit disciplina spremna za realnu upotrebu.
```

Moj dodatni predlog: nemoj da `tooling` “zove flow iz framework-a”. Obrni smer.

Bolja hijerarhija je:

```text
Git Hook
  -> Avax CLI command
      -> Framework PreCommit flow
          -> Internal checks
          -> External tooling adapters
              -> tooling scripts
```

Zašto? Zato što `tooling/` treba da bude kutija sa alatima, ne mozak sistema. Mozak je `PreCommit` capability. Tako
dobijaš čistiju arhitekturu: disciplina živi u framework-u, a skripte su samo izvršni mehanizmi. 🧩

I najbitnije: delete mora biti ekstremno oprezan. Detekcija legacy/deprecated/shim fajlova je odlična ideja, ali
auto-delete pre reference analize je klasična mina. Prvo report, zatim klasifikacija, tek onda eventualno brisanje.

---

## Production Readiness Recovery TODO - 2026-05-01

Ovo je operativni plan posle poslednjeg code review kruga. Trenutno stanje: osnovni framework gate-ovi su zeleni, ali
repo nije production ready jer `components/` static analiza i broken-reference audit i dalje imaju kritične nalaze.

### 0. Komande za trenutni baseline

Pokrenuti iz root-a repozitorijuma:

```bash
git status --short
git diff --stat

./vendor/bin/phpunit --no-coverage
./vendor/bin/phpstan analyse --memory-limit=1G --error-format=raw

php tooling/docs/validate-docs.php
php tooling/docs/validate-docs-mirror-source.php
php tooling/architecture/check-forbidden-folders.php
php tooling/check-superglobals.php
```

Ocekivan trenutni rezultat:

- PHPUnit zelen: 12 tests, 33 assertions.
- Framework PHPStan zelen za postojeći `phpstan.neon` scope.
- Docs, mirror docs, forbidden-folder i superglobal audit zeleni.

### 1. Komande koje otkrivaju production blokatore

Pokrenuti i sacuvati izlaze:

```bash
./vendor/bin/phpstan analyse components --memory-limit=1G --error-format=raw --no-progress > /tmp/avax-components-phpstan.raw 2>&1
php tooling/audit_broken_refs.php > /tmp/avax-broken-refs.raw 2>&1
./vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no > /tmp/avax-php-cs-fixer.diff 2>&1

wc -l /tmp/avax-components-phpstan.raw /tmp/avax-broken-refs.raw /tmp/avax-php-cs-fixer.diff
rg '^/home/shomsy/projects/avax/components/' /tmp/avax-components-phpstan.raw | cut -d '/' -f 7 | sort | uniq -c | sort -nr
rg '^MISSING:' /tmp/avax-broken-refs.raw | wc -l
rg '^MISSING:' /tmp/avax-broken-refs.raw | sed -n '1,80p'
```

Trenutni poznati rezultat:

- `components/` PHPStan: oko 8979 linija nalaza.
- Najveci delovi po komponentama: `Application`, `Identity`, `DataStack`, `Operations`, `HTTP`.
- Broken references audit: 615 missing referenci, 410 critical.
- PHP-CS-Fixer dry-run: 1846/2480 fajlova bi bilo menjano.

### 2. Prvi prioritet: components PHPStan mora biti zelen

Ne popravljati sve odjednom. Raditi po komponentama i posle svake grupe ponovo pokrenuti komponentni PHPStan.

- [ ] `components/Application/Cache`
    - Popraviti pogresne named argumente: `sources` vs `compiledCacheSources`, `key` vs `cacheKey`, `lifecycle` vs
      `cachedValueLifecycle`.
    - Popraviti undefined property pristupe, posebno `CacheStoreRecordWasFound::$record` i
      `CompiledCacheTarget::$sources`.
    - Popraviti `CacheResult`: duplo deklarisane readonly properties i factory metode koje koriste stare parametre
      `state`/`key`.
    - Dodati value types za iterable parametre i povratne vrednosti.

- [ ] `components/Application/Container`
    - Uskladiti stare namespace reference (`DI`, `DependencyInjection`, `Core`) sa canonical `System/...` rasporedom.
    - Popraviti named argumente u resolution/compile/runtime tokovima.
    - Ukloniti ili jasno oznaciti stare smoke/benchmark testove koji ciljaju nepostojece klase.
    - Dodati/azurirati contract testove tek posle stabilizacije public surface-a.

- [ ] `components/Identity`
    - Srediti `AuthBuilder` i `DefaultAuth` pre ostalih jer imaju najveci broj nalaza.
    - Uskladiti konfiguracione objekte, session store i public surface potpise.
    - Proveriti da nema zabranjenih foldera/namespaces i da su `System/Capabilities`, `System/Flows`,
      `System/Configuration`, `System/PublicSurface` dosledni.

- [ ] `components/DataStack`
    - Smanjiti legacy namespace drift: `DataFoundation`, `DataLayer`, `Database`, `Persistence`.
    - Odvojiti stvarno aktivan public API od compatibility bridge-a.
    - Ne brisati bridge dok broken-reference audit ne potvrdi da nije referenced.

- [ ] `components/Operations`
    - Proveriti ApplicationWorkflow/Saga, Resilience, Scheduler i RateLimiter.
    - Popraviti callable/Closure, DateTimeImmutable, iterable value types i named argumente.
    - Dodati ciljane testove za popravljene tokove.

- [ ] `components/HTTP`
    - Uskladiti Request/Response/Router namespace-ove.
    - Popraviti reference na nepostojece `Request`, `Response`, `Router`, `RouteDefinition`, exception i URI klase.
    - Proveriti da direktni superglobal pristup ostane samo u dozvoljenim boundary fajlovima.

### 3. Drugi prioritet: broken references audit

Komanda:

```bash
php tooling/audit_broken_refs.php > /tmp/avax-broken-refs.raw 2>&1
rg '^MISSING:' /tmp/avax-broken-refs.raw | wc -l
rg '^MISSING:' /tmp/avax-broken-refs.raw | sed -n '1,120p'
```

Popraviti redom:

- [ ] Critical interne reference koje pocinju sa `Avax\Components\...`.
- [ ] Stare `components\...` lowercase namespace reference.
- [ ] `Avax\DataFoundation\...` i `Avax\Components\DataFoundation\...` reference.
- [ ] Testove koji ciljaju klase koje vise ne postoje.
- [ ] Compatibility alias-e samo ako su jos uvek potrebni; ne brisati automatski.

Done kriterijum:

- `Missing: 0` za interne Avax/component reference, ili eksplicitno dokumentovan compatibility exception.

### 4. Treci prioritet: TODO/BUG liste moraju biti sinhronizovane

- [ ] `.agents/management/TODO.md`
    - Zatvoriti stare syntax stavke za `test_feature.php` nakon sto fajl ostane obrisan ili bude zamenjen validnim
      testom.
    - Zatvoriti stare `test_good_file.php` stavke ako fajl ne postoji i gate je zelen.
    - Security stavke zatvoriti tek kada precommit/security validator prodje na stvarnim staged fajlovima.

- [ ] `.agents/management/BUGS.md`
    - BUG-E006 do BUG-E016 su i dalje `open`, iako glavni TODO tvrdi da su taskovi completed.
    - Za svaki BUG proveriti source + test evidence, pa promeniti status u `fixed/closed` ili ga ostaviti kao aktivan
      blocker.

- [ ] `.agents/management/ACTIVE.md`
    - Ne sme da pise `No active cards` dok postoje open TODO/BUG stavke.
    - Posle azuriranja TODO/BUG liste, sinhronizovati board.

### 5. Cetvrti prioritet: style gate

Komanda:

```bash
./vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no > /tmp/avax-php-cs-fixer.diff 2>&1
```

Ne pokretati masovni fixer odmah. Prvo odluciti:

- [ ] Da li `.php-cs-fixer.dist.php` treba uskladiti sa lokalnim pravilom `string|null` umesto nullable `?string`.
- [ ] Da li style cleanup ide po komponentama, ne preko celog repozitorijuma odjednom.
- [ ] Da li se `.phpunit.cache/test-results` drzi van commit-a ili se uklanja iz tracking-a posebnom odlukom.

Done kriterijum:

- PHP-CS-Fixer dry-run ne prijavljuje promene za aktivni scope koji se commituje.

### 6. Zavrsni production-ready gate

Tek kada su gore navedene stavke zavrsene, pokrenuti komplet:

```bash
./vendor/bin/phpunit --no-coverage
./vendor/bin/phpstan analyse --memory-limit=1G --error-format=raw
./vendor/bin/phpstan analyse components --memory-limit=1G --error-format=raw --no-progress
php tooling/audit_broken_refs.php
php tooling/check-superglobals.php
php tooling/docs/validate-docs.php
php tooling/docs/validate-docs-mirror-source.php
php tooling/architecture/check-forbidden-folders.php
./vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no
```

Production-ready znaci:

- [ ] Svi gore navedeni gate-ovi su green.
- [ ] `components/` nema PHPStan critical/runtime nalaze.
- [ ] Broken-reference audit nema interne missing reference.
- [ ] TODO, BUGS i ACTIVE liste su medjusobno uskladjene.
- [ ] Nema novih forbidden foldera/namespaces: `Services`, `Helpers`, `Utils`, `Common`, `Shared`, `Managers`, `Core`,
  `Support`.
- [ ] Nema nedokumentovanih compatibility bridge-eva.
- [ ] Svaka menjana komponenta ima relevantan test ili jasan razlog zasto test nije dodat.
