# 0015. Queue and Worker Strategy

## Status

Accepted

## Context

Stari repozitorijum je sadržao dugačke fajlove za implementaciju redova: `Queue.php`, `Job.php`, `Batch.php`,
`Task.php`. Ovi fajlovi su simulirali potpun Queue engine sa drajverima (Redis, DB, itd.) i background procesingom.

Noviji Avax je dizajniran oko "worker-safe" arhitekture, pa su ovi mehanizmi došli u sukob: dugoživeći workeri unutar
samog framework-a već rukuiju rešavanjem zahteva asinhrono, pa nema potrebe za starim poling mehanizmima.

## Decision

1. Trenutno se opredeljujemo za **Postpone/Drop**. Nećemo ugrađivati Queue sistem u prvu (v0) stabilnu verziju
   runtime-agnostic Avax-a.
2. Fokusiramo se isključivo na `HandleWorkerRequest` flow i state resetovanje kako bismo osigurali sigurnost u
   Swoole/RoadRunner/FrankenPHP okruženjima.
3. Rad u pozadini i taskovi će kasnije biti razvijeni kao eksterna `Queue` komponenta koja se delegira server runtime-u
   ukoliko server to podržava, a ne rešava unutar čistog PHP polling-a.

## Consequences

- **Pozitivno:** Nema "fantomskih" procesa ni komplikovanih ugrađenih daemon runnera unutar web framework-a.
- **Negativno:** Za sada, framework ne poseduje asinhrono procesiranje iz kutije, što može zahtevati eksterne servise.
