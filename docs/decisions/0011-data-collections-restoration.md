# 0011. Data Collections Restoration Strategy

## Status

Accepted

## Context

U starom sistemu, kolekcije su bile modelovane kao masivni *God objekti* (`Collection.php`, `Arr.php`, `Enumerable.php`)
koji su pružali izuzetno moćne metode za manipulaciju nizovima (map, filter, reduce, flatMap, pluck). Gubitkom ovih
klasa, razvoj poslovne logike je postao previše otežan i "primitivan", jer se oslanja na sirove PHP array funkcije.

Sa druge strane, ne želimo da u novi čist sistem uvezemo monolitan fajl od 50KB koji meša iteratore, array access,
makroe i lazy load.

## Decision

Funkcionalnosti kolekcija vraćamo nazad pod `components/Data` vlasništvo, ali arhitektonski redizajnirano.

1. `components/Data/System/Capabilities/Collections/` će postati vlasnik ovih mehanizama.
2. Napravićemo čiste ugovore (Contracts) za osnovne operacije nad podacima.
3. Zajedno sa kolekcijama, vraćamo i `Arrhae` / `DataPath` za type-safe, deep-dot-notation čitanje nizova.
4. Metode će delegirati rad usko specijalizovanim klasama umesto da se sve nalazi u jednoj `Collection` klasi.

## Consequences

- **Pozitivno:** Developer Experience se drastično poboljšava.
- **Pozitivno:** `Data` komponenta dobija jasno vlasništvo nad transformacijom in-memory podataka.
- **Negativno:** Moramo napisati nove testove da bismo potvrdili da svedene kolekcije obavljaju posao bez curenja
  performansi.
