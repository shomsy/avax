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
