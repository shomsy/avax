Da. Evo finalnog, ozbiljnog plana, bez šuma.

Polazim od tri stvari koje su već jasne iz koda i governance pravila: prvo, Filesystem trenutno ima paralelne vlasnike za isti problem, kao što su `Contracts/FilesystemInterface`, `FileServiceInterface`, `Storage/FileStorageInterface`, root `FilesystemService`, `LocalFileService`, i `Storage/Filesystem`; drugo, postoje dupli koncepti i dupli exception slojevi; treće, dokumentacija i ownership shape moraju da budu eksplicitni, sa jednostavnim nazivima, malim public surface-om, bez generic bucket-a i bez lažnih sinonima. To direktno traže tvoja how-to pravila.     

Zato je ispravan cilj refaktora da Filesystem postane jedna jasna capability komponenta, sa jednim imenom po konceptu, sa konkretnim action-owner fajlovima, sa dokumentacijom koja mirroruje source shape, i sa staged refaktorom koji prvo štiti ponašanje testovima, pa tek onda menja shape. Behavior-changing refactor ne sme da se maskira kao “sređivanje tree-ja”, a velika promena mora da ide u malim, sigurnim koracima.   

Ovo je finalni target tree koji plan koristi:

```text
Filesystem/
  Filesystem.php
  FilesystemInterface.php
  AsyncFilesystemInterface.php

  Files/
    ReadFile.php
    WriteFile.php
    AppendToFile.php
    DeleteFile.php
    CopyFile.php
    MoveFile.php
    ReadFileLastModifiedAt.php

    FileNotFound.php
    FileWriteFailed.php
    FileDeleteFailed.php
    FileCopyFailed.php
    FileMoveFailed.php

  Directories/
    CreateDirectory.php
    EnsureDirectoryExists.php
    EnsureDirectoryIsWritable.php
    DeleteDirectory.php
    ListDirectoryFiles.php
    ClearDirectory.php

    DirectoryCreateFailed.php
    DirectoryDeleteFailed.php
    DirectoryClearFailed.php

  Paths/
    PathExists.php
    PathIsWritable.php
    PathIsDirectory.php
    ChangePathPermissions.php
    PathHasPermissions.php

  Disks/
    Disk.php
    DiskDefinition.php
    ResolveDisk.php
    UnsupportedDiskDriver.php

    Local/
      LocalDisk.php

  Configuration/
    FilesystemConfig.php
    RegisterFilesystem.php

  docs/
    how-this-works.md

    Files/
      how-this-works.md

    Directories/
      how-this-works.md

    Paths/
      how-this-works.md

    Disks/
      how-this-works.md

      Local/
        how-this-works.md

    Configuration/
      how-this-works.md

  tests/
    Files/
    Directories/
    Paths/
    Disks/
    Configuration/

  tooling/
    merge-files.sh

  README.md
  AGENTS.md
```

Ovaj shape je pošteniji od trenutnog zato što root ostavlja mali public surface, capability folderi govore šta sistem koristi, a file-ovi govore tačno koju odgovornost nose. To je bukvalno u skladu sa pravilima “folder says flow or capability, unit says responsibility, function says exact action”, “one concept, one name”, i zabranom generic bucket-a i paralelnog imenovanja za isti koncept.    

Detaljan plan refaktora

1. Stabilizacija pre bilo kakvog pomeranja

Prvi korak nije rename, nego zaključavanje postojećeg ponašanja. Trenutni Filesystem ima dosta realnog behavior-a koji mora da ostane isti dok menjaš strukturu: čitanje, upis, append, delete, create directory, clear directory, writable checks, permissions, disk resolution, i local disk operacije. Po pravilima koje si poslao, refaktor mora prvo da zaštiti correctness i change safety, a tek onda da sređuje shape.   

U ovoj fazi praviš characterization testove za postojeće ponašanje, čak i kada je API ružan. Posebno zaključaj ova ponašanja: `read()` baca grešku za nepostojeći i nereadable file; `write()` automatski kreira parent directory; `write(..., append: true)` dodaje sadržaj; `delete()` smatra nepostojeći fajl uspešno obrisanim; `deleteDirectory()` smatra nepostojeći direktorijum uspešno obrisanim; `clear()` briše sadržaj; `hasPermission()` poredi permission bits; `disk()` baca grešku za unsupported driver. Tek kada to imaš pod testom, smeš da sečeš stare apstrakcije.  

2. Uvođenje novog public surface-a bez rušenja starog koda

Drugi korak je da uvedeš novi root public surface, ali privremeno bez brisanja starog koda. U root-u ostaju samo `Filesystem.php`, `FilesystemInterface.php` i `AsyncFilesystemInterface.php`. To je stabilan, mali, dosadan public surface kakav governance traži. `Contracts/` folder se gasi kao root concept, jer ovde ne dodaje arhitektonsku vrednost nego samo uvodi još jedan hallway između korisnika i komponente.   

Ovde radiš i semantičko poravnanje root API-ja. Najvažniji rez je da `write(..., bool $append = false)` više ne ostane centralni API signal, jer su boolean flag argumenti design smell i sakrivaju dve različite akcije iza jedne metode. Zato u novom shape-u `WriteFile` i `AppendToFile` postaju odvojeni action owner-i, a root `Filesystem` može da ih delegira kroz odvojene metode ili da `write()` ostane samo kao canonical overwrite path dok append dobije sopstveni entrypoint.  

3. Sečenje duplih apstrakcija i mapiranje jednog koncepta na jedno ime

Ovo je najvažniji deo. Trenutno imaš najmanje tri paralelna sloja za istu ideju filesystem rada, što governance direktno zabranjuje kroz “parallel naming for the same concept” i “one concept, one name”. Zato praviš jednu čistu mapu koncepta i brišeš sinonime.   

Precizna mapa je ova:

* `Contracts/FilesystemInterface.php` ide u root kao `FilesystemInterface.php`
* `Contracts/AsyncFilesystemInterface.php` ide u root kao `AsyncFilesystemInterface.php`
* `Storage/FileStorageInterface.php` ne ostaje kao public concept, nego se pretvara u `Disks/Disk.php`
* `Storage/Filesystem.php` se ne zadržava kao sekundarni public facade, nego se njegova orkestracija seli u root `Filesystem.php`
* `LocalFileService.php` se gasi
* `FileServiceInterface.php` se gasi
* `FilesystemService.php` se gasi
* `DirectoryInitializer.php` se razlaže na `EnsureDirectoryExists.php` i `EnsureDirectoryIsWritable.php`
* `Storage/LocalFileStorage.php` postaje `Disks/Local/LocalDisk.php`
* root `Exceptions/*` i `Storage/*Exception.php` se svode na jedinstvene exception fajlove u ownership folderima gde su stvarno korišćeni. 

Suština je jednostavna: ne smeš više da imaš i “service”, i “storage”, i “filesystem”, i “file service” za istu odgovornost. Jedan koncept, jedno ime, jedan owner.  

4. Ekstrakcija realnih capability zona

Kad očistiš sinonime, sledeći korak je da implementaciju rasporediš po stvarnim capability zonama. Tu ne izmišljaš dubinu zbog estetike, nego pratiš realne odgovornosti. Governance kaže da hijerarhija postoji samo kada smanjuje noise i kada svaka zona priča malu, poštenu priču.  

`Files/` dobija isključivo file actions i file failures. Tu idu `ReadFile`, `WriteFile`, `AppendToFile`, `DeleteFile`, eventualno `CopyFile`, `MoveFile`, `ReadFileLastModifiedAt`, plus file-specific exception klase. Ništa directory-related ne ulazi tu. 

`Directories/` dobija samo directory lifecycle i directory traversal operacije. `CreateDirectory`, `EnsureDirectoryExists`, `EnsureDirectoryIsWritable`, `DeleteDirectory`, `ClearDirectory`, `ListDirectoryFiles`, i directory-specific failure klase. Tu prirodno završava ono što je danas sakriveno u `DirectoryInitializer` i pola `LocalFileStorage`.  

`Paths/` dobija path-level checks i permission stvari koje nisu ni file ni directory lifecycle u užem smislu. Tu idu `PathExists`, `PathIsWritable`, `PathIsDirectory`, `ChangePathPermissions`, `PathHasPermissions`. To je čist capability boundary, jer se koristi kao osnovni signal za više zona.  

`Disks/` ostaje kao mehanizam za backend storage driver-e, ali tek sada pošteno imenovan. `Disk.php` je interni ugovor za disk implementation, `ResolveDisk.php` bira disk, `DiskDefinition.php` nosi konfiguracioni opis, a `Local/LocalDisk.php` je konkretan lokalni driver. Trenutni `Storage/Filesystem::disk()` koristi globalni `config()` i `app()`, što jasno pokazuje da disk resolution jeste zaseban capability i ne treba da bude sakriven u drugom facade-u.  

`Configuration/` dobija samo assembling. Tu ide `FilesystemConfig.php` i `RegisterFilesystem.php`. Po pravilima, composition pripada configuration zoni, ne runtime action fajlovima. Zato disk wiring, default driver registration i binding-i ne smeju da ostanu u `Filesystem.php` ili u `LocalDisk.php`.  

5. Prepis postojećeg runtime ponašanja u novi shape

Sada, kada je shape spreman, radiš stvarni prepis implementacije. Ovde je redosled važan.

Prvo implementiraš `LocalDisk.php` tako da privremeno nosi sve low-level operacije koje danas žive u `LocalFileStorage`, ali kroz internu, uredniju podelu po action owner fajlovima. `LocalDisk` treba da bude backend boundary, ne bog-klasa. Zato je dozvoljeno da `ReadFile`, `WriteFile`, `DeleteFile`, `CreateDirectory`, i slični action owner-i internto koriste `LocalDisk` ili obrnuto, ali ownership mora da bude jasan i bez dva paralelna entrypoint-a za isto ponašanje.  

Drugo, root `Filesystem.php` postaje jedini user-facing facade. On ne radi storage logiku sam, nego delegira ka novim action owner-ima i disk resolution-u. Važno je da root surface ostane mali i čitljiv, a implementation machinery da ostane iza njega. To je tačno u skladu sa tvojim standardom za reusable package komponente.  

Treće, `DirectoryInitializer` se ne prenosi kao klasa u novom shape-u. Njegov behavior se deli na dve poštene akcije: jedna garantuje postojanje direktorijuma, druga proverava i potvrđuje writability kroz probni write signal. To je čistiji ownership nego trenutna auto-magijska konstruktorska logika koja radi više stvari odjednom.  

6. Exception model, jedan failure jezik za ceo paket

Trenutno imaš exception klase i u root `Exceptions/` i u `Storage/` namespace-u, što je čist primer duplog jezika za isti problem. To mora da nestane. Svaka failure klasa treba da postoji jednom, na mestu gde se najpoštenije koristi. Nazivi moraju da budu banalni i predvidivi.  

Pravila za exceptions u ovom refaktoru su:

* file operation errors idu u `Files/`
* directory operation errors idu u `Directories/`
* disk selection error ide u `Disks/UnsupportedDiskDriver.php`
* nema duplih `FileNotFoundException` u dva namespace-a
* nema generic `RuntimeException` kad već imaš svoj domain-level failure
* poruke grešaka moraju da objasne šta je puklo i koji path ili driver je problem.  

7. Test strategija, ne testiraj privatnu mehaniku nego javno ponašanje

Testovi u ovom refaktoru nisu ukras. Oni su osigurač protiv nenamernog behavioral drift-a. How-to pravila eksplicitno traže da refaktor poboljša testability i da promene budu zaštićene testovima.  

Zato testovi treba da budu organizovani po istom capability tree-ju:

* `tests/Files/` za read, write, append, delete, copy, move, last modified
* `tests/Directories/` za create, ensure exists, ensure writable, list, clear, delete
* `tests/Paths/` za exists, isWritable, isDirectory, permissions
* `tests/Disks/` za disk resolution, unsupported driver, local disk integration
* `tests/Configuration/` za wiring i default driver registration. 

Posebno dodaj abuse i edge testove za security-relevant i boundary-relevant ponašanje: nepostojeći path, unreadable file, unwritable directory, invalid driver config, path permission mismatch, delete non-existent path, clear non-directory, nested directory delete, i append behavior. To je direktno u duhu security i safety pravila iz governance-a.  

8. Dokumentacija, paralelno sa svakim ownership folderom

Ovde nema pregovora. Tvoj `docs/` mora da bude jedina canonical documentation lokacija, mora da mirroruje source shape, i svaki ownership folder mora da ima `how-this-works.md` sa obaveznom strukturom i realnim file/function path-ovima. To je hard rule u dokumentacionim pravilima koje si uploadovao.    

Za Filesystem to znači:

* `docs/how-this-works.md` objašnjava root public surface, disk resolution priču, i reading order komponente
* `docs/Files/how-this-works.md` objašnjava file action ownership
* `docs/Directories/how-this-works.md` objašnjava lifecycle i traversal direktorijuma
* `docs/Paths/how-this-works.md` objašnjava path checks i permission checks
* `docs/Disks/how-this-works.md` objašnjava disk resolution i disk contract
* `docs/Disks/Local/how-this-works.md` objašnjava local backend implementation
* `docs/Configuration/how-this-works.md` objašnjava wiring i default registration.  

9. Završno brisanje mrtvih struktura

Tek kada novi shape radi i testovi prolaze, radiš aktivno brisanje starih struktura. How-to architecture je ovde vrlo jasan: kada uvedeš bolji ownership, stare fake abstractions, obsolete layers, deprecated synonyms i duplicate owners treba da se obrišu, ne da ostanu kao fosili. 

To znači da na kraju moraju da nestanu:

* `Contracts/`
* `Storage/` kao root architectural slice
* `FileServiceInterface.php`
* `LocalFileService.php`
* `FilesystemService.php`
* `DirectoryInitializer.php`
* duple exception klase u starim namespace-ovima
* svi compatibility alias-i koji čuvaju staru terminologiju bez stvarne potrebe.  

10. Poslednja arhitektonska validacija

Na kraju radiš hladan review kroz governance checklist. Pitanja su jednostavna: da li folder govori capability, da li unit jasno nosi jednu odgovornost, da li postoji jedan koncept i jedno ime, da li je shape čitljiviji nego pre, da li je behavior pokriven testovima, da li docs govore istinu o stvarnom kodu, i da li je uklonjena arhitektonska buka. Ako bilo koje od toga padne, refaktor još nije gotov.  

ToDo lista za sve

Ispod je konkretan ToDo, redom kojim bih ga zaista radio.

```text
PHASE 1, Lock current behavior
[ ] Dodaj characterization test za Storage/LocalFileStorage::read()
[ ] Dodaj characterization test za Storage/LocalFileStorage::write() overwrite mode
[ ] Dodaj characterization test za Storage/LocalFileStorage::write() append mode
[ ] Dodaj characterization test za Storage/LocalFileStorage::delete()
[ ] Dodaj characterization test za Storage/LocalFileStorage::createDirectory()
[ ] Dodaj characterization test za Storage/LocalFileStorage::deleteDirectory()
[ ] Dodaj characterization test za Storage/LocalFileStorage::clear()
[ ] Dodaj characterization test za Storage/LocalFileStorage::exists()
[ ] Dodaj characterization test za Storage/LocalFileStorage::isWritable()
[ ] Dodaj characterization test za Storage/LocalFileStorage::setPermissions()
[ ] Dodaj characterization test za Storage/LocalFileStorage::hasPermission()
[ ] Dodaj characterization test za Storage/LocalFileStorage::listFiles()
[ ] Dodaj characterization test za Storage/Filesystem::disk()
[ ] Dodaj characterization test za Storage/Filesystem facade delegaciju
[ ] Dodaj characterization test za DirectoryInitializer behavior
[ ] Dodaj characterization test za LocalFileService behavior
[ ] Dodaj characterization test za FilesystemService behavior

PHASE 2, Create target structure
[ ] Kreiraj folder Files/
[ ] Kreiraj folder Directories/
[ ] Kreiraj folder Paths/
[ ] Kreiraj folder Disks/
[ ] Kreiraj folder Disks/Local/
[ ] Kreiraj folder Configuration/
[ ] Kreiraj folder docs/
[ ] Kreiraj folder tests/
[ ] Kreiraj folder tooling/

PHASE 3, Introduce new root public surface
[ ] Kreiraj root FilesystemInterface.php
[ ] Premesti Contracts/FilesystemInterface.php u root i poravnaj namespace
[ ] Kreiraj root AsyncFilesystemInterface.php
[ ] Premesti Contracts/AsyncFilesystemInterface.php u root i poravnaj namespace
[ ] Kreiraj novi root Filesystem.php kao jedini public facade
[ ] Definiši jasan public API za root Filesystem
[ ] Odluči da li root public API izlaže append kao posebnu metodu
[ ] Ukloni bool flag semantiku iz centralnog API dizajna

PHASE 4, Files capability
[ ] Kreiraj Files/ReadFile.php
[ ] Kreiraj Files/WriteFile.php
[ ] Kreiraj Files/AppendToFile.php
[ ] Kreiraj Files/DeleteFile.php
[ ] Kreiraj Files/CopyFile.php
[ ] Kreiraj Files/MoveFile.php
[ ] Kreiraj Files/ReadFileLastModifiedAt.php
[ ] Kreiraj Files/FileNotFound.php
[ ] Kreiraj Files/FileWriteFailed.php
[ ] Kreiraj Files/FileDeleteFailed.php
[ ] Kreiraj Files/FileCopyFailed.php
[ ] Kreiraj Files/FileMoveFailed.php
[ ] Premesti read behavior iz LocalFileStorage u novi Files ownership
[ ] Premesti write overwrite behavior iz LocalFileStorage u novi Files ownership
[ ] Premesti append behavior iz LocalFileStorage u novi Files ownership
[ ] Premesti delete behavior iz LocalFileStorage u novi Files ownership
[ ] Implementiraj last modified read behavior
[ ] Ako copy i move danas ne postoje, uvedi ih samo ako su stvarno deo scope-a

PHASE 5, Directories capability
[ ] Kreiraj Directories/CreateDirectory.php
[ ] Kreiraj Directories/EnsureDirectoryExists.php
[ ] Kreiraj Directories/EnsureDirectoryIsWritable.php
[ ] Kreiraj Directories/DeleteDirectory.php
[ ] Kreiraj Directories/ListDirectoryFiles.php
[ ] Kreiraj Directories/ClearDirectory.php
[ ] Kreiraj Directories/DirectoryCreateFailed.php
[ ] Kreiraj Directories/DirectoryDeleteFailed.php
[ ] Kreiraj Directories/DirectoryClearFailed.php
[ ] Razbij DirectoryInitializer na EnsureDirectoryExists i EnsureDirectoryIsWritable
[ ] Premesti create directory behavior iz LocalFileStorage i LocalFileService
[ ] Premesti delete directory behavior iz LocalFileStorage
[ ] Premesti clear directory behavior iz LocalFileStorage
[ ] Premesti listFiles behavior iz LocalFileStorage
[ ] Odluči da li listanje nepostojećeg direktorijuma vraća [] ili baca exception, i to zaključi testovima

PHASE 6, Paths capability
[ ] Kreiraj Paths/PathExists.php
[ ] Kreiraj Paths/PathIsWritable.php
[ ] Kreiraj Paths/PathIsDirectory.php
[ ] Kreiraj Paths/ChangePathPermissions.php
[ ] Kreiraj Paths/PathHasPermissions.php
[ ] Premesti exists behavior u Paths ownership
[ ] Premesti writable checks u Paths ownership
[ ] Premesti directory check behavior u Paths ownership
[ ] Premesti permission change behavior u Paths ownership
[ ] Premesti permission comparison behavior u Paths ownership
[ ] Ukloni direct chmod noise iz viših nivoa

PHASE 7, Disks capability
[ ] Kreiraj Disks/Disk.php
[ ] Kreiraj Disks/DiskDefinition.php
[ ] Kreiraj Disks/ResolveDisk.php
[ ] Kreiraj Disks/UnsupportedDiskDriver.php
[ ] Kreiraj Disks/Local/LocalDisk.php
[ ] Pretvori Storage/FileStorageInterface.php u Disks/Disk.php
[ ] Pretvori Storage/LocalFileStorage.php u Disks/Local/LocalDisk.php
[ ] Premesti Storage/Filesystem::disk() logiku u Disks/ResolveDisk.php
[ ] Izvuci driver selection iz root facade-a gde je potrebno
[ ] Izoluj globalni config/app pristup iza configuration ili disk resolution boundary-ja
[ ] Obezbedi da LocalDisk ne postane nova bog-klasa

PHASE 8, Configuration lane
[ ] Kreiraj Configuration/FilesystemConfig.php
[ ] Kreiraj Configuration/RegisterFilesystem.php
[ ] Premesti default disk config čitanje u Configuration lane
[ ] Premesti dependency wiring u Configuration lane
[ ] Obezbedi da root Filesystem ne sadrži assembling logiku
[ ] Obezbedi da LocalDisk ne zna za container registration

PHASE 9, Exception cleanup
[ ] Ukloni root Exceptions/DirectoryCreationException.php ako ostane duplikat
[ ] Ukloni root Exceptions/DirectoryDeletionException.php ako ostane duplikat
[ ] Ukloni root Exceptions/FileDeleteException.php ako ostane duplikat
[ ] Ukloni root Exceptions/FileNotFoundException.php ako ostane duplikat
[ ] Ukloni root Exceptions/FileWriteException.php ako ostane duplikat
[ ] Ukloni Storage/FileNotFoundException.php
[ ] Ukloni Storage/FileWriteException.php
[ ] Zameni generic RuntimeException tamo gde postoji domain-level failure
[ ] Standardizuj poruke svih exception klasa
[ ] Standardizuj namespace i ownership svih failure tipova

PHASE 10, Remove obsolete structures
[ ] Ukloni Contracts/ folder
[ ] Ukloni Storage/ folder kao arhitektonski root
[ ] Ukloni FileServiceInterface.php
[ ] Ukloni LocalFileService.php
[ ] Ukloni FilesystemService.php
[ ] Ukloni DirectoryInitializer.php
[ ] Ukloni stare namespace use-ove i import-e
[ ] Ukloni kompatibilnostne alias-e ako više nisu potrebni
[ ] Ukloni svaki mrtav kod put posle migracije

PHASE 11, Tests by target tree
[ ] Kreiraj tests/Files/ReadFileTest.php
[ ] Kreiraj tests/Files/WriteFileTest.php
[ ] Kreiraj tests/Files/AppendToFileTest.php
[ ] Kreiraj tests/Files/DeleteFileTest.php
[ ] Kreiraj tests/Files/CopyFileTest.php
[ ] Kreiraj tests/Files/MoveFileTest.php
[ ] Kreiraj tests/Files/ReadFileLastModifiedAtTest.php
[ ] Kreiraj tests/Directories/CreateDirectoryTest.php
[ ] Kreiraj tests/Directories/EnsureDirectoryExistsTest.php
[ ] Kreiraj tests/Directories/EnsureDirectoryIsWritableTest.php
[ ] Kreiraj tests/Directories/DeleteDirectoryTest.php
[ ] Kreiraj tests/Directories/ListDirectoryFilesTest.php
[ ] Kreiraj tests/Directories/ClearDirectoryTest.php
[ ] Kreiraj tests/Paths/PathExistsTest.php
[ ] Kreiraj tests/Paths/PathIsWritableTest.php
[ ] Kreiraj tests/Paths/PathIsDirectoryTest.php
[ ] Kreiraj tests/Paths/ChangePathPermissionsTest.php
[ ] Kreiraj tests/Paths/PathHasPermissionsTest.php
[ ] Kreiraj tests/Disks/ResolveDiskTest.php
[ ] Kreiraj tests/Disks/Local/LocalDiskTest.php
[ ] Kreiraj tests/Configuration/RegisterFilesystemTest.php
[ ] Dodaj edge case testove za nepostojeće path-ove
[ ] Dodaj edge case testove za permission failure
[ ] Dodaj edge case testove za unsupported driver
[ ] Dodaj edge case testove za unreadable file
[ ] Dodaj edge case testove za unwritable directory
[ ] Dodaj regression testove za append/newline behavior

PHASE 12, Documentation
[ ] Kreiraj docs/how-this-works.md
[ ] Kreiraj docs/Files/how-this-works.md
[ ] Kreiraj docs/Directories/how-this-works.md
[ ] Kreiraj docs/Paths/how-this-works.md
[ ] Kreiraj docs/Disks/how-this-works.md
[ ] Kreiraj docs/Disks/Local/how-this-works.md
[ ] Kreiraj docs/Configuration/how-this-works.md
[ ] Dodaj obavezni frontmatter u svaki how-this-works.md
[ ] U svaki how-this-works.md ubaci real file/function imena
[ ] U svaki how-this-works.md ubaci real trigger ili upstream handoff
[ ] U svaki how-this-works.md ubaci prvi važan path kao sequenceDiagram
[ ] U svaki how-this-works.md napiši šta ulazi, šta izlazi, šta se upisuje, šta korisnik vidi
[ ] U svaki how-this-works.md napiši gde prvo debug-ovati
[ ] Proveri da docs mirroruje source shape bez rupa

PHASE 13, Tooling and repo hygiene
[ ] Premesti merge-files.sh u tooling/
[ ] Dokumentuj zašto tooling/ postoji i šta merge-files.sh radi
[ ] Obezbedi da tooling ne curi u runtime package surface
[ ] Ažuriraj README.md da prati novi shape
[ ] Ažuriraj AGENTS.md ako referencira stare putanje ili stare nazive

PHASE 14, Final validation
[ ] Prođi checklist ownership clarity
[ ] Prođi checklist one concept one name
[ ] Prođi checklist forbidden generic buckets
[ ] Prođi checklist docs completeness
[ ] Prođi checklist test coverage po ponašanju
[ ] Prođi checklist dead code removal
[ ] Prođi checklist root public surface simplicity
[ ] Prođi checklist configuration isolation
[ ] Prođi checklist disk resolution honesty
[ ] Potvrdi da nema više paralelnih facade-a za isti koncept
```

Moj praktični savet je da ovaj refaktor radiš u 6 commit talasa: prvo characterization tests, drugo novi root surface i target folderi, treće Files + Directories, četvrto Paths + Disks, peto docs + tooling, šesto delete pass. Tako ćeš zadržati kontrolu nad blast radius-om i vrlo lako ćeš videti gde si eventualno uveo novo zamućenje. To je daleko zdravije nego da odradiš jedan ogroman rename + rewrite commit.  
