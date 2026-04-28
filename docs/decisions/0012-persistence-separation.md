# 0012. Persistence vs Database Separation

## Status

Accepted

## Context

Stari Database / Query sloj je bio monolitan i mešao je dve potpuno različite odgovornosti:

1. **Low-level mehanika baze:** Konekcije, raw query izvršavanja, transakcije, migracije, schema building.
2. **High-level mapiranje objekata (ORM):** UnitOfWork, IdentityMap, Repositories, Hydration, Change Tracking.

Ovaj monolit (*Database owns ORM*) je dovodio do toga da entiteti zavise od konekcije, a baza mora da razume kompleksne
grafove objekata.

## Decision

Uvodimo striktno razdvajanje odgovornosti u dve nezavisne komponente:

1. **`components/Database`**:
    - Postaje isključivi vlasnik komunikacije sa bazom.
    - Odgovornosti: Connection Pool, Advanced Query Builder, Transactions, Savepoints, Schema/Migrations.
    - Ne sme da zna za bilo kakve entitete ili objekte. Vraća čiste nizove ili generičke objekte.

2. **`components/Persistence`**:
    - Vraća stare ORM funkcionalnosti ali kao arhitektonski nezavisan sloj nad bazom.
    - Odgovornosti: `UnitOfWork`, `IdentityMap`, `Repository` contract, objektna `Hydration` i `Change Tracking`.
    - Zavisi od `Database` ugovora, ali štiti ostatak aplikacije od sirovog SQL-a.

## Consequences

- **Pozitivno:** Entiteti ostaju framework-agnostic.
- **Pozitivno:** Transakcije i *savepointi* se sada mogu testirati bez ORM overheda.
- **Negativno:** Nešto viša krivulja učenja jer developeri moraju da nauče razliku između `Database Query` i
  `Persistence Repository`.
