# 0014. Date and Time Strategy

## Status

Accepted

## Context

Ranije verzije projekta sadržale su `Carbon.php` (preko 160KB) i njegove prateće interfejse kao core deo repozitorijuma.
Iako ovo olakšava rad sa vremenom (human-readable razlike, dodavanje nedelja, parsiranje formata), to je ogroman *vendor
bloat* i prečesto vezuje logiku aplikacije za statičke pozive poput `Carbon::now()`, što otežava unit testiranje.

## Decision

1. **Ne vraćamo `Carbon`** u Avax Core.
2. Upravljanje vremenom će se raditi preko `Foundation/Time/Clock` abstrakcije zasnovane na PSR-20 (`ClockInterface`) i
   nativnih PHP `DateTimeImmutable` objekata.
3. Injektovanje `Clock` objekta postaje standard za testiranje kako bi se eliminisali nedeterministički testovi.
4. Aplikacije koje žele `Carbon` mogu ga instalirati preko composer-a i implementirati `ClockInterface` adapter za
   Carbon ako im je to neophodno.

## Consequences

- **Pozitivno:** Drastično manji footprint koda.
- **Pozitivno:** Determinističko i lakše testiranje zahvaljujući `ClockInterface`-u.
- **Negativno:** Nedostatak out-of-the-box helpera za *diffForHumans* ili složenu višejezičnu obradu datuma.
