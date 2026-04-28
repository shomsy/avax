# 0013. View Engine Strategy

## Status

Accepted

## Context

U staroj arhitekturi, Avax je direktno koristio masivne portove templating engine-a poput `BladeOne.php` (fajlovi preko
150KB). Takav pristup krši jedno od osnovnih pravila novog framework-a: ne pretvarati teške eksterne zavisnosti (vendor
bloat) u *core* framework funkcionalnost.

## Decision

1. Avax će interno implementirati **samo bazični, jednostavan View Renderer** zasnovan na nativnom PHP-u.
2. Ovaj osnovni renderer će podržavati čiste varijable, izolaciju scope-a i osnovne layout-e (`extends` / `yield`
   pristup).
3. Kompleksni sistemi poput Blade-a, Twig-a i ostale magije neće biti vraćeni u Core.
4. Ako projekat zahteva Blade, on mora biti ubačen kao **Adapter** u `components/View/System/Capabilities/Adapters`, a
   ne kao sastavni deo framework baze.

## Consequences

- **Pozitivno:** Framework postaje lakši za stotine kilobajta i nezavisan od eksternih engine-a.
- **Pozitivno:** Rendering views procesa u worker okruženjima je brži jer koristi native PHP.
- **Negativno:** Razvojni tim mora ručno kreirati Blade adapter ukoliko žele da koriste napredne Blade direktive na
  specifičnom projektu.
