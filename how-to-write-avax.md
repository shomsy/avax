Najpre postuj AGENTS.md i .agents, tu je srz SDLC-a.

Koristi striktno sva how-to-*.md pravila dok radis, svako ali bas svako pravilo striktno !!! Enterpise grade kvalitet je
koji zelim, nista manje.
Zato iskoristi priliku da naucis svako moguce pravilo i memorises i iskorisis svako da bude striktno, hard governance.

Sve sto radis mora da bude po TODO.md (nalazi se u root-u avax-a) i po EXECUTION.md i how-to-*.md dokumentima. Znaci sve
sto je u tim fajlovima mora da se sprovede u delo. Nemoj nista da izbacujes iz implementacije samo zato sto ti se
nesvidja. Sve mora da bude kako treba da bi avax bio framework koji je "READY TO USE!" a najvise od svega - PRODUCTION
READY !!! Vodi evidenciju sta radis, na cemu trenutno radis u TODO.md, ona sluzi za trenutno stanje. Neka tu bude
vidljiva istorija da bi mogao neko da nastavi tamo gde ti stanes. Tu napisi sta je gotovo sta je WIP i sta je polovicno
odradjeno. Checkbox-ovi su najbolja varijanta za todo liste.

Probaj u tooling da nadjes neku skriptu koja resava stvari na kojima radis, ili napravi neku svoju. Poenta je da time
skratis proces rada i da ga optimizujes. Zato je tooling i napravljen. Ako umes i mozes, napisi Rector custom pravila za
AvaX framework, da proverava sta treba i kako treba da radi, da postuje how-to-*.md standarde, ali i da ih fix-ira, ako
moze da pomogne i ubrza rad na taj nacin. Takodje mozes da napravis skriptu u nekom drugom programskom jeziku - Golang,
Rust, NodeJS, Python, ili slicno, verovatno je da se brze izvrsavaju od PHP-a, jer PHP nije holy grail za tooling.
Takodje, ako postoji neka biblioteka za resavanje nekih problema, implementiraj je slobodno, samo me obavesti, negde je
dokumentuj da znamo sta su nam dependecies projekta.

Uvek preispituj da li implementacija prati instrukcije iz TODO.md iz root-a avax foldera?
Ako ne, ispravi sve da to bude tako. Da li prati instrukcije EXECUTION.md file-a iz EVIDENCE foldera? Da li
prati sam plan koji trenutno razvijamo (na pr. avax-master-development-plan-v1.md ili v2 li v3). Striktno treba da uradis da bude 1:1 sa tim
governace-om iz planova - zato ako u njima postoji project tree uporedi trenutnu implementaciju za planom da li je 1:1 struktura. Ako plan odstupa od how-to-*.md pravila (naming, arhitektura, OOP, kod..), obavesti me.

Recover radis iz EVIDENCE/archive foldera, tu ti je avax-backup.txt jer je skorija istorija. Mozes da pogledas i main branch takodje, i njegovu istoriju. Osim toga, praistorija framework-a jer u Framework.txt i Components.txt. Izvuci sve sto mozes odatle i ubaci da bude po planu V1, V2, V3, V4... S tim u vezi, ne moras da pises od nule vec da iskoristis postojeci kod, makar kao ideju, inspiraciju i zamisao ali implementiraj po logici trenutnog plana. Ovi fajlovi se nikako ne smeju brisati. Resavaj sve redom u nizu svaki next allowed action bez moje dozovole - verujem ti. Hajde da zatvorimo V1, V2, V3, V4 plan i recovery, da bude sve cisto i production ready. Drzi se how-to-*.md striktno, kao i ostalih .agents pravila.