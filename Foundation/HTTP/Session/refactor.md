


Ovo treba da se radi kao **ciljani redesign uz strogo cuvanje ponasanja**, ne kao “malo rename-ujemo tree”. Tvoja pravila traze da struktura bude flow-first / capability-first, da genericki bucketi poput `Core`, `Shared`, `Managers` budu odbaceni kao default, da public surface bude mali i stabilan, i da refaktor ide kroz karakterizacione testove i male bezbedne korake. Trenutni Session je jos uvek organizovan oko `Session` facade + `CoreManager` / `RecoveryManager` / `AuditManager` / `EventsManager`, dok `SessionEngine` u sebi vec nosi storage, encryption, recovery, policy enforcement, registry, audit, events, login i lifecycle. To je previse pritiska na jednu osu i previse generickih bucketa za tvoj governance standard. Zato je ispravan sledeci potez: **Redesign**, ne rewrite, i ne kozmetika.    

Ispod je plan koji bih ja zakucao kao radni master plan.

## 1. Cilj refaktora

Cilj nije samo da Session “izgleda lepse”. Cilj je da komponenta pocne da se cita ovim redom:

`public surface -> flow slice -> capability slice -> file owner -> exact action`

To direktno prati tvoju glavnu arhitektonsku zakonitost: folder kaze flow ili capability, unit kaze responsibility, function kaze exact action. Uz to, za biblioteku je bitno da spolja bude mala i stabilna, a unutra strogo organizovana po ownership-u.  

## 2. Glavna odluka oko modela

Session komponentu bih vodio sa dve jasno odvojene zone:

Prva zona je **public surface**. Tu ostaju samo `Session.php`, `SessionInterface.php` i `SessionScope.php` kao tanka, stabilna ulazna vrata.

Druga zona je **unutrasnja struktura** koja se deli na:

* flow slices, za stvari koje Session radi
* capability slices, za shared mehanizme koje Session koristi
* configuration, za assembly i wiring
* foundation, za male neutralne primitive

To je tacno onaj placement framework koji trazis: prvo flow ako je lokalno, onda capability ako je stvarno shared, pa configuration ako je assembly, pa foundation ako je tiny primitive.

## 3. Najvaznija korekcija domenske ose

Najbitniji rez je ovaj:

**Session ne sme da bude prikriveni Auth.**

U trenutnom kodu `SessionEngine` ima `login()` koji radi regeneraciju ID-a, upis user podataka, registry registraciju, audit i event dispatch. To je vec domenski prelaz iz session ownership-a u auth ownership. Session sme da pomogne autenticiranom toku, ali ne sme da postane mesto gde se auth semantika razliva po engine-u. Zato umesto `login()` kao centralne session price, uvodimo `BindSessionActor/` kao iskreniji session boundary. Auth kasnije moze da zove Session, ali Session ne glumi Auth.  

## 4. Redosled rada

### Faza 0. Zamrzavanje ponasanja

Pre bilo kakvog pomeranja fajlova, napravi karakterizacione testove nad danasnjim javnim ponasanjem. Tvoja pravila su eksplicitna: za refaktor prvo karakterizacija, pa refaktor iza tih testova, pa postepeno ciscenje. Bez toga je ovo hazard.

Karakterizacioni testovi moraju da zakucaju barem ovo:

* `put / get / has / forget / flush`
* `remember`
* `scope / for`
* `regenerate`
* `terminate`
* `flash` lifecycle
* `snapshot / restore / export / import`
* `events` side effects
* `audit` side effects
* security cases za encryption, policy enforcement i registry activity

Za security-relevant delove dodaj abuse i failure-mode testove, ne samo happy path.

### Faza 1. Uvedi novu ciljnu strukturu bez brisanja starog sveta

Prvo napravi novi tree koji smo definisali, ali bez agresivnog prebacivanja logike prvog dana. Poenta je da se dobije nova semanticka mapa sistema, a da stare klase jos privremeno rade kao adapteri. Tvoja pravila dozvoljavaju privremene legacy adaptere kada smanjuju blast radius, ali ne dozvoljavaju da ostanu zauvek.

U ovoj fazi napravi prazne ili minimalne root ownere za sledece flow-e:

* `ReadSessionValue`
* `WriteSessionValue`
* `DeleteSessionValue`
* `ClearSession`
* `RememberSessionValue`
* `RegenerateSessionId`
* `TerminateSession`
* `FlashSession`
* `BindSessionActor`
* `TakeSessionSnapshot`
* `RestoreSessionSnapshot`
* `ExportSessionState`
* `ImportSessionState`
* `BeginSessionTransaction`

I capability-je:

* `SessionStore`
* `SessionCookie`
* `SessionEvents`
* `SessionAudit`
* `SessionRecovery`
* `SessionRegistry`
* `SessionSecurity`
* `Configuration`
* `Foundation`

### Faza 2. Iskopaj `SessionEngine`

`SessionEngine` je trenutno glavni pritisak sistema. On vec sada povezuje store, config, encrypter, recovery, id provider, audit, events, signature, policy, registry, http context, plus sadrzi login/get/put/regenerate/terminate logiku. To je textbook signal da owner vise nije iskren. 

Zato bih ga razbio ovako:

* logika citanja ide u `ReadSessionValue/`
* logika pisanja ide u `WriteSessionValue/`
* lifecycle ide u `RegenerateSessionId/` i `TerminateSession/`
* recovery ide u `TakeSessionSnapshot/`, `RestoreSessionSnapshot/`, `BeginSessionTransaction/`, `ExportSessionState/`, `ImportSessionState/`
* registry logika ide u `SessionRegistry/`
* policy enforcement, nonce, signature, encryption i session id idu u `SessionSecurity/`
* sam storage boundary ostaje u `SessionStore/`

`SessionEngine` na kraju ne treba da prezivi kao “tajni kernel”. Ako ti ostane ikakav centralni owner, neka to bude mali facade ili composition root, ne boga-klasa.

### Faza 3. Ubij genericke buckete

Ovo je obavezno, ne estetski. `Core`, `Shared`, `Managers` moraju napolje zato sto direktno krse naming law i anti-bucket pravila. Isto vazi za paralelne sinonime. Jedan koncept, jedno ime.

Konkretno:

* `Core/Config.php` prelazi u `Configuration/SessionConfig.php`
* `Core/CoreManager.php` se gasi i raspakuje u flow ownere
* `Audit/AuditManager.php` se gasi, ostaje `SessionAudit/RecordSessionAudit.php` i eventualno `SessionAudit.php` kao capability boundary
* `Recovery/RecoveryManager.php` se gasi i raspakuje po recovery flow-ovima
* `Events/EventsManager.php` se gasi i menja sa `SessionEventBus.php` + action ownerima
* `Shared/Contracts/...` i `Shared/Security/...` se raspakuju u precizne capability zone

### Faza 4. Stabilizuj public API

Spoljni API mora ostati dosadan i predvidiv. To je dobro. Ne treba spolja izlagati 40 klasa. `Session` i `SessionScope` treba da budu jedina uobicajena ulazna vrata, uz eventualno `SessionInterface`. Unutra oni delegiraju na nove root ownere po flow-u. Public surface ostaje mali i stabilan, internals ostaju sakriveni.

Dakle:

* `Session::put()` delegira na `WriteSessionValue`
* `Session::get()` delegira na `ReadSessionValue`
* `Session::forget()` na `DeleteSessionValue`
* `Session::flush()` na `ClearSession`
* `Session::remember()` na `RememberSessionValue`
* `Session::regenerate()` na `RegenerateSessionId`
* `Session::terminate()` na `TerminateSession`
* `Session::scope()` vraca `SessionScope`
* `SessionScope` samo prefiksira namespace i dalje delegira na iste flow ownere

### Faza 5. Security ownership dovedi u red

Security se ne sme skrivati po random helperima i usputnim granama. Tvoja pravila traze da bezbednost zivi tamo gde je iskreno owned.

Zato u `SessionSecurity/` jasno odvoji:

* `SessionId/`
* `SessionNonce/`
* `SessionFingerprint/`
* `SessionSignature/`
* `SessionEncryption/`
* `SessionPolicy/`

I vrlo bitno: policy enforcement ne treba da bude “malo ovde, malo onde” u raznim metodama. Treba da bude predvidiv i dosledan. Ako svaki read/write/regenerate/terminate ima policy gate, to treba da bude ocigledno u flow owneru, ne zakopano duboko u nekoj nejasnoj generickoj klasi.

### Faza 6. Recovery ucini iskrenim

Recovery danas vec ima snapshot/import/export/transaction semantiku. To je prava capability zona, ali operacije koje korisnik stvarno radi su flow-ovi. Zato capability zadrzava infrastrukturu snapshot / transaction store, a flow-ovi dobijaju zasebne ownere.

Dakle:

* capability: `SessionRecovery/SessionRecovery.php`, `SessionSnapshotStore.php`, `SessionTransactionStore.php`
* flow: `TakeSessionSnapshot`, `RestoreSessionSnapshot`, `ExportSessionState`, `ImportSessionState`, `BeginSessionTransaction`

To drasticno cisti citljivost.

### Faza 7. Dokumentacija ide paralelno sa strukturom

Ovde nema pregovora. Tvoja dokumentacijska pravila su jasna:

* sve ide pod top-level `docs/`
* docs mirror-uju source shape
* svaki ownership folder dobija `how-this-works.md`
* dokumentacija mora da objasni zasto nesto postoji, ne samo kako radi

Zato svaka faza refaktora mora da ima i docs izlaz:

* `docs/Session/how-this-works.md`
* `docs/Session/ReadSessionValue/how-this-works.md`
* `docs/Session/WriteSessionValue/how-this-works.md`
* itd.

Nemoj da ostavis docs “za kraj”. To skoro uvek zavrsi kao lazni sistemski opis.

### Faza 8. Brisanje legacy sveta

Kad novi flow owners prodju kroz testove i facade prestane da koristi stare klase, stare slojeve treba aktivno obrisati. Tvoja pravila su i tu vrlo jasna: kad uvedes bolje ownership-e, obsolete patterns se brisu, ne cuvaju radi sentimentalnosti. 

To znaci:

* obrisi `Core/`
* obrisi `Managers`
* obrisi `Shared` ako je ostao samo kao hallway
* obrisi deprecated sinonime
* obrisi compatibility forwarding nakon kratkog migracionog prozora

## 5. Precizan ToDo

Evo ToDo-a kako bih ga dao AI-u ili sebi.

### A. Safety first

1. Napravi characterization test suite za postojece javno Session ponasanje.
2. Zakljucaj sve side-effect scenarije za audit, events, registry i recovery.
3. Dodaj security abuse tests za invalid encrypted payload, expired TTL, policy rejection, terminated session, revoked session i invalid import payload.

### B. New structure skeleton

4. Napravi novi root tree sa public surface, flow slices, capability slices, configuration i foundation.
5. Napravi prazne `how-this-works.md` fajlove za svaki ownership folder pod `docs/`.
6. U `docs/Session/how-this-works.md` opisi staro as-built stanje i ciljnu novu strukturu.

### C. Public surface normalization

7. Zadrzi `Session.php`, `SessionInterface.php`, `SessionScope.php` kao jedina glavna entrypoints.
8. Ukloni iz public surface-a svako nepotrebno curenje internals.
9. Uvedi dosledno delegiranje iz `Session` facade-a na nove flow ownere.

### D. Flow extraction

10. Izvuci `ReadSessionValue` iz `SessionEngine::get`.
11. Izvuci `WriteSessionValue` iz `SessionEngine::put`.
12. Izvuci `DeleteSessionValue` iz `CoreManager::forget`.
13. Izvuci `ClearSession` iz `CoreManager::flush`.
14. Izvuci `RememberSessionValue` iz `CoreManager::remember`.
15. Izvuci `RegenerateSessionId` iz `SessionEngine::regenerate`.
16. Izvuci `TerminateSession` iz `SessionEngine::terminate`.
17. Napravi `BindSessionActor` kao Session-owned boundary umesto da `login()` ostane u engine-u. 

### E. Recovery extraction

18. Izvuci `TakeSessionSnapshot`.
19. Izvuci `RestoreSessionSnapshot`.
20. Izvuci `ExportSessionState`.
21. Izvuci `ImportSessionState`.
22. Izvuci `BeginSessionTransaction`, `CommitSessionTransaction`, `RollBackSessionTransaction`.
23. Ogradi recovery storage i serializer odgovornosti unutar `SessionRecovery/`.

### F. Capability cleanup

24. Premesti store interfejse i implementacije u `SessionStore/`.
25. Premesti cookie concerns u `SessionCookie/`.
26. Premesti event bus i listener ownership u `SessionEvents/`.
27. Premesti audit ownership u `SessionAudit/`.
28. Premesti registry ownership u `SessionRegistry/`.
29. Premesti sve security mehanizme u `SessionSecurity/`.
30. Premesti config VO i assembly u `Configuration/`.
31. Premesti tiny neutrals u `Foundation/` i ne dozvoli da postane helper smetliste.

### G. Naming cleanup

32. Ukloni `Core`, `Shared`, `Manager`, `Support` i slicne bucket nazive.
33. Ukloni paralelne sinonime za isti koncept.
34. Svedi svaki file na jednu ociglednu odgovornost po nazivu.
35. Svedi funkcije na exact action naming. `read`, `write`, `drop`, `restore`, `verify`, `encrypt`, `decrypt`, `register`, `revoke`.

### H. Contract and correctness cleanup

36. Uvedi jasne typed DTO / VO granice tamo gde primitivna zbrka pocinje da muti smisao.
37. Ucini failure paths eksplicitnim. Tvoj clean-code governance eksplicitno odbacuje silent failure bez namere.
38. Proveri sve exception tokove i import collisione. U snippetu se vec vidi sumnjiv `RecoveryManager` import duplog `RecoveryException`, a `AuditManager::disable()` zove `terminate()` na `Audit` iako to nije vidljivo u prikazanom `Audit` API-ju. To su tacno oni signali koje treba ispeglati tokom karakterizacije i extraction-a.

### I. Documentation and decision records

39. Za svaki ownership folder dopuni `how-this-works.md` sa real trigger path-om, first important path, failure shape i debug entry point-om.
40. Dodaj kratke decision records za ove odluke:

* zasto `login()` vise nije centralni Session owner
* zasto je `SessionEngine` rasformiran
* zasto je public surface ostao mali
* zasto su `Core/Shared/Managers` obrisani
* zasto recovery ostaje capability + flow hybrid model

### J. Legacy deletion

41. Kad svi testovi prodju na novoj putanji, obrisi stare managers.
42. Obrisi stari `Core/` layer.
43. Obrisi svaki legacy forwarding method koji vise ne cuva pravi compatibility value.
44. Obrisi mrtve sinonime, mrtve docs i mrtve import path-ove. 

## 6. Sta bih ja smatrao “done”

Refaktor je zavrsen tek kad mozes da kazes sledece:

* root Session se cita u 10 sekundi
* novi timski clan bez otvaranja 20 fajlova moze da nadje gde se cita, pise, regenerise i terminira session
* nema `Core`, `Shared`, `Manager` bucketa
* nema boga-klase koja drzi pola sistema
* security ownership je ocigledan
* recovery ownership je ocigledan
* docs mirror-uju source shape
* tests cuvaju ponasanje
* stari layer-i su fizicki obrisani, ne samo “deprecated” komentarisani

To je standard koji tvoji dokumenti zapravo traze. Nista manje.

A ovo je **finalni target tree** koji bih ja zakucao za Session komponentu.

Bitna stvar, ovo je **ciljna arhitektura za refactor**, nije slepo preslikavanje trenutnog stanja. Namerno odbacuje `Core`, `Shared`, `Managers` i slicne buckete, jer tvoja pravila traze flow/capability ownership, malu javnu povrsinu, jasne granice i banalno precizno imenovanje. Trenutni Session je jos uvek manager/engine-centric, sa `Session` facade-om koji delegira na `CoreManager`, `RecoveryManager`, `AuditManager`, `EventsManager`, dok `SessionEngine` vuce previse odgovornosti. Zato je ispravan smer redesign u flow-first shape, ne ulepsavanje starog tree-ja.     

```text
HTTP/
  Session/
    Session.php
    SessionInterface.php
    SessionScope.php

    ReadSessionValue/
      ReadSessionValue.php
      ResolveSessionReadKey.php
      ReadStoredSessionValue.php
      RejectExpiredSessionValue.php
      DecryptSessionValue.php
      ReadFlashSessionValue.php

    WriteSessionValue/
      WriteSessionValue.php
      ResolveSessionWriteKey.php
      EncryptSessionValue.php
      PersistSessionValue.php
      PersistSessionTtl.php
      PublishSessionValueStored.php
      RecordSessionValueStored.php
      RefreshSessionActivity.php

    DeleteSessionValue/
      DeleteSessionValue.php
      ResolveSessionDeleteKey.php
      DropStoredSessionValue.php
      PublishSessionValueDeleted.php
      RecordSessionValueDeleted.php
      RefreshSessionActivity.php

    ClearSession/
      ClearSession.php
      DropAllStoredSessionValues.php
      PublishSessionCleared.php
      RecordSessionCleared.php

    CheckSessionValue/
      CheckSessionValue.php
      ResolveSessionCheckKey.php

    ReadAllSessionValues/
      ReadAllSessionValues.php

    RememberSessionValue/
      RememberSessionValue.php
      ReadRememberedSessionValue.php
      ComputeMissingSessionValue.php
      PersistRememberedSessionValue.php

    RegenerateSessionId/
      RegenerateSessionId.php
      ReadCurrentSessionId.php
      CreateNextSessionId.php
      ReplaceCurrentSessionId.php
      PublishSessionIdRegenerated.php
      RecordSessionIdRegenerated.php

    TerminateSession/
      TerminateSession.php
      DropSessionCookie.php
      DropStoredSessionValues.php
      UnregisterActiveSession.php
      PublishSessionTerminated.php
      RecordSessionTerminated.php

    FlashSession/
      FlashSession.php
      PutFlashSessionValue.php
      ReadFlashSessionValue.php
      KeepFlashSessionValue.php
      DropFlashSessionValue.php
      SweepFlashSessionValues.php
      PromoteFlashSessionValues.php
      FlashSessionBag.php

    BindSessionActor/
      BindSessionActor.php
      ReadBoundSessionActor.php
      DropBoundSessionActor.php
      PersistBoundSessionActor.php
      RegisterActorSession.php
      PublishSessionActorBound.php
      RecordSessionActorBound.php
      SessionActor.php

    TakeSessionSnapshot/
      TakeSessionSnapshot.php
      CreateSessionSnapshot.php
      PersistSessionSnapshot.php
      RecordSessionSnapshotTaken.php

    RestoreSessionSnapshot/
      RestoreSessionSnapshot.php
      ReadSessionSnapshot.php
      ReplaceSessionStateFromSnapshot.php
      PublishSessionSnapshotRestored.php
      RecordSessionSnapshotRestored.php

    ExportSessionState/
      ExportSessionState.php
      SerializeSessionState.php
      RecordSessionStateExported.php

    ImportSessionState/
      ImportSessionState.php
      ValidateImportedSessionState.php
      UnserializeSessionState.php
      ReplaceImportedSessionState.php
      RecordSessionStateImported.php

    BeginSessionTransaction/
      BeginSessionTransaction.php
      SessionTransaction.php
      BeginTrackedSessionTransaction.php
      CommitSessionTransaction.php
      RollBackSessionTransaction.php
      RecordSessionTransactionCommitted.php
      RecordSessionTransactionRolledBack.php

    SessionStore/
      SessionStore.php
      StoredSessionValue.php
      ArraySessionStore.php
      NativeSessionStore.php
      FileSessionStore.php
      RedisSessionStore.php
      SessionStoreKey.php
      SessionStoreScope.php
      SessionStoreTtl.php
      SessionStoreLock/
        SessionStoreLock.php
        FileSessionStoreLock.php
        RedisSessionStoreLock.php

    SessionCookie/
      SessionCookie.php
      SessionCookieSettings.php
      SessionCookieName.php
      SessionCookieLifetime.php
      WriteSessionCookie.php
      ExpireSessionCookie.php
      HardenSessionCookie.php

    SessionEvents/
      SessionEventBus.php
      SessionEvent.php
      PublishSessionEvent.php
      ListenToSessionEvent.php
      ListenOnceToSessionEvent.php
      StopListeningToSessionEvent.php
      SessionEventName.php

    SessionAudit/
      SessionAudit.php
      RecordSessionAudit.php
      SessionAuditPayload.php
      SessionAuditSink.php
      MaskSensitiveSessionAuditFields.php

    SessionRecovery/
      SessionRecovery.php
      SessionSnapshotStore.php
      SessionTransactionStore.php
      SessionStateSerializer.php
      SessionStateChecksum.php

    SessionRegistry/
      SessionRegistry.php
      RegisterActiveSession.php
      UnregisterActiveSession.php
      RevokeSession.php
      RevokeAllActorSessions.php
      ReadActorSessions.php
      RefreshRegistryActivity.php
      ActiveSessionRecord.php

    SessionSecurity/
      SessionId/
        SessionId.php
        SessionIdGenerator.php
        SessionIdProvider.php
        SessionIdValidator.php

      SessionNonce/
        SessionNonce.php
        ReadSessionNonce.php
        RefreshSessionNonce.php

      SessionFingerprint/
        SessionFingerprint.php
        ReadSessionFingerprint.php
        VerifySessionFingerprint.php

      SessionSignature/
        SessionSignature.php
        SignSessionState.php
        VerifySessionStateSignature.php

      SessionEncryption/
        SessionEncrypter.php
        SessionEncryptionKey.php
        EncryptSessionValue.php
        DecryptSessionValue.php

      SessionPolicy/
        SessionPolicy.php
        EnforceSessionPolicy.php
        CompositeSessionPolicy.php
        IdleTimeoutPolicy.php
        AbsoluteLifetimePolicy.php
        SecureTransportPolicy.php
        BindSessionToIpPolicy.php
        BindSessionToUserAgentPolicy.php
        ConcurrentSessionLimitPolicy.php

    SessionErrors/
      SessionException.php
      SessionValueExpired.php
      SessionEncryptionKeyMissing.php
      SessionImportRejected.php
      SessionPolicyViolation.php
      SessionSnapshotMissing.php
      SessionTransactionFailed.php
      SessionStoreFailure.php

    Configuration/
      SessionConfig.php
      SessionCookieConfig.php
      SessionSecurityConfig.php
      SessionRegistryConfig.php
      SessionRecoveryConfig.php
      BuildSession.php
      RegisterSessionBindings.php

    Foundation/
      Clock.php
      UuidGenerator.php
      RandomBytesGenerator.php
      JsonCodec.php
      SafeSerializer.php
      SafeUnserializer.php

  docs/
    Session/
      how-this-works.md
      Session.md
      SessionScope.md

      ReadSessionValue/
        how-this-works.md
      WriteSessionValue/
        how-this-works.md
      DeleteSessionValue/
        how-this-works.md
      ClearSession/
        how-this-works.md
      CheckSessionValue/
        how-this-works.md
      ReadAllSessionValues/
        how-this-works.md
      RememberSessionValue/
        how-this-works.md
      RegenerateSessionId/
        how-this-works.md
      TerminateSession/
        how-this-works.md
      FlashSession/
        how-this-works.md
      BindSessionActor/
        how-this-works.md
      TakeSessionSnapshot/
        how-this-works.md
      RestoreSessionSnapshot/
        how-this-works.md
      ExportSessionState/
        how-this-works.md
      ImportSessionState/
        how-this-works.md
      BeginSessionTransaction/
        how-this-works.md
      SessionStore/
        how-this-works.md
      SessionCookie/
        how-this-works.md
      SessionEvents/
        how-this-works.md
      SessionAudit/
        how-this-works.md
      SessionRecovery/
        how-this-works.md
      SessionRegistry/
        how-this-works.md
      SessionSecurity/
        how-this-works.md
      SessionErrors/
        how-this-works.md
      Configuration/
        how-this-works.md
      Foundation/
        how-this-works.md

  tests/
    Session/
      PublicSurface/
        SessionTest.php
        SessionScopeTest.php

      Flows/
        ReadSessionValueTest.php
        WriteSessionValueTest.php
        DeleteSessionValueTest.php
        ClearSessionTest.php
        CheckSessionValueTest.php
        ReadAllSessionValuesTest.php
        RememberSessionValueTest.php
        RegenerateSessionIdTest.php
        TerminateSessionTest.php
        FlashSessionTest.php
        BindSessionActorTest.php
        TakeSessionSnapshotTest.php
        RestoreSessionSnapshotTest.php
        ExportSessionStateTest.php
        ImportSessionStateTest.php
        BeginSessionTransactionTest.php

      Capabilities/
        SessionStoreTest.php
        SessionCookieTest.php
        SessionEventsTest.php
        SessionAuditTest.php
        SessionRecoveryTest.php
        SessionRegistryTest.php
        SessionSecurityTest.php

      Security/
        SessionPolicyTest.php
        SessionSignatureTest.php
        SessionNonceTest.php
        SessionFingerprintTest.php
        SessionEncryptionTest.php
        SessionIdValidatorTest.php

      Integration/
        NativeSessionFlowTest.php
        FileSessionFlowTest.php
        RedisSessionFlowTest.php
        SessionRecoveryFlowTest.php
        SessionTerminationFlowTest.php
```

Kako da citas ovaj tree:

`Session/` root je namerno mali. On je javna povrsina komponente. Tu ne sme da se prospe pola sistema. To direktno prati pravilo da public API bude mali, stabilan i boring, dok internals ostaju iza jasnih granica.   

Flow folderi, tipa `ReadSessionValue`, `WriteSessionValue`, `RegenerateSessionId`, `TerminateSession`, `ImportSessionState`, postoje zato sto je Session pre svega skup radnji koje korisnik radi nad session state-om. To je mnogo iskrenije od `CoreManager` i mnogo citljivije od jednog engine-a koji radi sve.   

`FlashSession/`, `BindSessionActor/`, `TakeSessionSnapshot/`, `RestoreSessionSnapshot/`, `ExportSessionState/`, `ImportSessionState/`, `BeginSessionTransaction/` su izdvojeni jer su to realne sposobnosti koje trenutni Session vec pokriva ili eksplicitno pominje kroz flash, recovery, import/export i transakcione tokove. Nisam ih izmislio iz vazduha, samo sam ih rasporedio u posteniji ownership model.  

Capability folderi postoje samo tamo gde je concern zaista shared. `SessionStore`, `SessionCookie`, `SessionEvents`, `SessionAudit`, `SessionRecovery`, `SessionRegistry`, `SessionSecurity` jesu realne deljene sposobnosti sistema i imaju smisla kao capability zone. To je u skladu i sa tvojim architecture pravilima i sa onim sto trenutni Session vec reklamira kao podsisteme.    

`SessionSecurity/` je namerno duboko razbijen. Trenutni Session vec ima encryption, nonce, signature, registry, policy, fixation protection i druge security concerns. Ti concerns ne smeju da budu posuti po helperima ili sakriveni u random engine granama. Ovde svaki bezbednosni mehanizam dobija svoje jasno vlasnistvo.  

`Configuration/` je composition zona. Tu ide assembly, wiring i config VO-i. Tvoja pravila su vrlo jasna da composition pripada configuration-u, a ne foundation-u ili random helperima. `Foundation/` ostaje mali i dosadan, samo za sitne primitive poput clock-a, random bytes, serializer codec-a i slicno.  

`docs/` mirroruje source tree i svaki ownership folder dobija `how-this-works.md`, jer tvoja dokumentacijska pravila to eksplicitno zahtevaju. Bez toga struktura nije zavrsena, samo je prepakovana. 

`tests/` su rasporedjeni po public surface, flows, capabilities, security i integration zonama, jer tvoji standardi traze da testovi stite behavior, contracts, invariants, security boundaries i javni API, a ne private implementation detalje. 

Moja najtvrda odluka ovde je sledeca: `login()` vise ne bih tretirao kao centralni Session flow. Session treba da ume da veze aktera za sesiju, da regenerise ID, da cuva state i da ga terminira. Auth neka radi autentikaciju. Session neka radi session. Zato sam stavio `BindSessionActor/`, a ne `Login/`. To je cisci boundary. Trenutni Session snippet pokazuje da se auth i session ownership trenutno mesaju u engine/provider sloju, a to dugorocno muti model. 

Ako hoces, sledece ti mogu dati **isti ovaj tree, ali sa kratkim opisom svakog pojedinacnog file-a, jedan po jedan, kao implementacionu mapu za Codex**.

