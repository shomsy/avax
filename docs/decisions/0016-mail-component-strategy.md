# 0016. Mail Component Strategy

## Status

Accepted

## Context

Stari repozitorijum je u Trash-u ostavio `Mail.php`, `Mailer.php`, `Notification.php`, i gomilu kodiranja za SMTP
integraciju. Komunikacija e-poštom i obaveštenjima je korisna za projekte, ali nije fundamentalni *Capability* samog
framework runtime-a.

## Decision

1. Mail sloj se **odbacuje (Dropped)** iz Core-a za trenutnu v0 fazu stabilizacije.
2. Održavanje sopstvenih SMTP klijenata i MIME formatera je rasipanje energije.
3. U kasnijim fazama, ukoliko Avax treba da pošalje mail, biće definisan apstraktan `MailerInterface` unutar
   `components/Mail/System/PublicSurface`, a iza njega će stajati `Symfony Mailer` u obliku adaptera. Avax više nikada
   neće sadržati native mail driver kod.

## Consequences

- **Pozitivno:** Eliminacija tehničkog duga i sigurnosnih rupa vezanih za parsiranje i slanje e-mailova iz nule.
- **Negativno:** Razvojni tim gubi "magični" `Mail::send()` ukoliko prethodno ne povuče odgovarajući paket.
