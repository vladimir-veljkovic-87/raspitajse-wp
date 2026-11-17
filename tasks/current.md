# Zadatak 2.69.2 — Potvrdi staging zaštite bez čitanja DB konfiguracije

Status: READY
Staging baseline: f6093c11d71ddc175ebf83fc69453c20aaca5239
Release candidate branch: feature/task-2.68.2-wp-7.1.2-release-preparation
Release candidate HEAD: 0c233ad646d7f8f0bac1079eb21f7e73b43d29b0
Previous task: 2.69.1 — PARTIAL
Production: FORBIDDEN
Risk class: AUTONOMOUS_SAFE
Mutation class: BOUNDED_TECHNICAL_HOUSEKEEPING
Deployment: FORBIDDEN
Time budget: 45 minutes

## Cilj

Završi jednu kontrolisanu staging WordPress runtime proveru iz 2.69/2.69.1.

Prethodni zadatak nije otkrio novi runtime problem. Zaustavljen je samo zato što je pokušaj čitanja `DB_HOST` radi klasifikacije transporta odbijen pravilom koje zabranjuje čitanje `wp-config.php`.

Ovaj zadatak unapred klasifikuje normalnu baznu konekciju koju postojeća staging WordPress instalacija koristi kroz tačno odobrenu staging putanju kao:

`APPROVED_INTERNAL_STAGING_DB_TRANSPORT`

Nije potrebno niti dozvoljeno utvrđivati, čitati ili prikazivati DB host, port, socket, username, password ili bilo koju `wp-config.php` vrednost.

Obavezni uspešan rezultat:

`PASS: STAGING_RUNTIME_PROTECTIONS_ATTESTED_WITH_CACHE_TRANSPORT_DISABLED`

Ne deployuj, ne pravi snapshot, ne menjaj Git grane i ne pristupaj produkciji.

## Šta ovaj zadatak izričito superseduje

Superseduje samo sledeći neuspešni precondition iz 2.69.1:

- čitanje ili klasifikovanje `DB_HOST`;
- dokazivanje da je DB host loopback, Unix socket ili određeni hostname.

Postojeća DB konekcija WordPressa iz odobrene staging putanje je unapred odobren interni staging servis.

Ovo odobrenje:

- važi samo za staging WordPress root;
- ne važi za produkcijsku bazu;
- ne dozvoljava čitanje konfiguracije;
- ne dozvoljava proizvoljne SQL upise;
- ne dozvoljava prikaz kredencijala;
- ne klasifikuje Redis, Memcached, HTTP, SMTP ili druge sockete kao dozvoljene.

## Obavezni kontekst

Pročitaj u celosti:

- `AGENTS.md`;
- `CODEX_WORKFLOW.md`;
- `CODEX_HOST_RESILIENCE.md`;
- `STAGING_MUTATION_POLICY.md`;
- `STAGING_DEFINITION_OF_DONE.md`;
- izveštaje 2.68.3, 2.69 i 2.69.1.

Prihvati statički dokaz iz 2.69.1 da LiteSpeed `_connect()` proverava `LITESPEED_OC_FAILURE` pre Redis/Memcached transporta. Ponovo proveri relevantne hash vrednosti, ali ne ponavljaj `strace`, `ptrace`, namespace probu ili DB host klasifikaciju.

## Preflight

Bez WordPress bootstrapa potvrdi:

- `origin/staging=f6093c11d71ddc175ebf83fc69453c20aaca5239`;
- release candidate remote HEAD je `0c233ad646d7f8f0bac1079eb21f7e73b43d29b0`;
- working tree je čist;
- staging marker je regularan non-symlink fajl sa tačnim baseline-om;
- staging realpath je tačno `/home/u601262303/domains/raspitajse.com/public_html/public_html_stage`;
- nema konkurentnog Git, rsync, deploy, snapshot, restore, core-update, WP-Cron ili queue-runner procesa;
- MU-plugin, object-cache drop-in i LiteSpeed hash vrednosti odgovaraju izveštaju 2.69.1.

Procesni dokaz sme sadržati samo PID, command name, state i elapsed time.

### Stroga zabrana konfiguracionog čitanja

Ne pokreći:

- `wp config get`;
- `wp config list`;
- `cat`, `grep`, `sed`, PHP include ili bilo koji drugi pokušaj čitanja `wp-config.php`;
- komandu koja prikazuje DB host, port, ime baze, korisnika ili lozinku.

WP-CLI sme interno koristiti postojeću konfiguraciju samo kao deo normalnog staging-scoped DB ili WordPress rada, bez prikazivanja vrednosti.

## Eksplicitno dozvoljene mutacije

Tokom jednog WordPress bootstrapa dozvoljeno je isključivo:

- uklanjanje postojećih instanci:
  - `elementor/tracker/send_event`;
  - `monsterinsights_feature_feedback_checkin`;
  - `wpforms_email_summaries_cron`;
- odgovarajući update isključivo staging `cron` optiona;
- in-process cache i DB fallback neophodan za jedan bootstrap;
- task-private fajlovi pod `/tmp/raspitajse-task-2.69.2-*`;
- nonblocking preuzimanje propisanih lockova;
- normalna staging DB konekcija, klasifikovana kao `APPROVED_INTERNAL_STAGING_DB_TRANSPORT`.

Nije dozvoljeno:

- menjanje drugog cron događaja;
- izvršavanje cron ili Action Scheduler callbacka;
- Redis/Memcached transport;
- spoljni HTTP/SMTP ili drugi nekontrolisani transport;
- poslovni, korisnički, content, application, order ili payment upis;
- promena staging fajlova, plugina, drop-ina, konfiguracije ili markera.

## T0 cron dokaz bez WordPress bootstrapa

Koristi staging-scoped `wp db query` ili ekvivalentni WP-CLI DB podnaredbeni put koji ne učitava WordPress plugine.

WP-CLI sme interno koristiti postojeću staging konfiguraciju, ali nijedna konfiguraciona vrednost ne sme biti prikazana ili kopirana.

Pročitaj samo `cron` option i odmah ga prosledi task-private PHP helperu. Ne prikazuj sirovi/serijalizovani sadržaj, callback argumente ili privatne podatke.

Zabeleži samo:

- broj događaja za svaki od tri dozvoljena hooka;
- ukupan broj cron događaja;
- SHA-256 kanonskog cron stanja nakon logičkog uklanjanja samo tri dozvoljena hooka.

Ako se sanitizovani dokaz ne može dobiti bez prikaza sadržaja, STOP pre bootstrapa.

## Preload guard

U svežem regularnom non-symlink direktorijumu `/tmp/raspitajse-task-2.69.2-<id>` napravi minimalan PHP preload guard koji pre WordPress učitavanja definiše, ako već nisu definisane:

- `LITESPEED_OC_FAILURE=true`;
- `DISABLE_WP_CRON=true`;
- `AUTOMATIC_UPDATER_DISABLED=true`;
- `WP_AUTO_UPDATE_CORE=false`;
- `WP_HTTP_BLOCK_EXTERNAL=true`;
- `SAVEQUERIES=true`.

Guard mora:

- proći `php -l`;
- imati zabeležen SHA-256;
- ne čitati/štampati konfiguraciju;
- ne registrovati događaje;
- ne slati mail;
- ne otvarati transport;
- ne menjati bazu.

Dokaži da WP-CLI `--require` učitava guard pre WordPress bootstrapa. Ako to nije dokazivo, STOP.

## Lock protokol

Nonblocking preuzmi redom:

1. `/tmp/raspitajse-staging-owned-cron-runner.lock`;
2. `/tmp/raspitajse-staging-runtime.lock`.

Ako neki lock nije odmah dostupan, oslobodi prethodni i objavi:

`PARTIAL: STAGING_RUNTIME_LOCK_BUSY`

Oslobađanje je obrnutim redom.

## Jedina dozvoljena runtime proba

Pokreni tačno jedan staging-scoped WP-CLI bootstrap sa:

- preload guardom;
- `--skip-plugins`;
- `--skip-themes`;
- bez cron ili Action Scheduler izvršavanja.

MU plugini i drop-in ostaju učitani.

Bezbedno izmeri samo:

- `wp_get_environment_type()`;
- relevantne MU-plugin basenames;
- zaštitne `function_exists`, `has_action` i `has_filter` rezultate/prioritete;
- boolean vrednosti guard konstanti;
- `wp_using_ext_object_cache()` kao boolean;
- agregirane SQL kategorije;
- agregirane transport kategorije.

Ne prikazuj SQL tekst, option sadržaj, konfiguracione vrednosti, hostove, portove, korisničke podatke ili tajne.

### Cache/transport acceptance

Potvrdi:

- `LITESPEED_OC_FAILURE === true`;
- `wp_using_ext_object_cache() === false` posle `muplugins_loaded`;
- LiteSpeed `_connect()` nije mogao doći do Redis/Memcached transport grane;
- `WP_HTTP_BLOCK_EXTERNAL === true`;
- nema drugog direktnog transport puta u učitanom scope-u.

### Mail safety acceptance

Potvrdi:

- `raspitajse_staging_mail_safety_is_staging()` postoji i vraća aktivno stanje;
- zaštite na `pre_wp_mail`, `wp_mail` i `phpmailer_init` registrovane su na očekivanim prioritetima.

### Cron recovery acceptance

Potvrdi:

- `raspitajse_staging_cron_recovery_guard_is_active()` postoji i vraća aktivno stanje;
- `DISABLE_WP_CRON === true`;
- očekivani telemetry/reporting callbackovi i filteri su registrovani.

### Action Scheduler acceptance

Potvrdi:

- `raspitajse_staging_disable_action_scheduler_async_dispatch` je registrovan;
- svi postojeći `pre_as_*` DROP filteri su registrovani;
- queue runner i Action Scheduler akcije nisu izvršeni.

## SQL klasifikacija

Interno pregledaj `SAVEQUERIES`, ali ne prikazuj SQL.

Jedini dozvoljeni mutating SQL je update staging `cron` optiona potreban da ukloni T0 instance tri dozvoljena hooka.

Svaki drugi mutating ili neklasifikovan SQL je:

`FAIL: UNEXPECTED_BOOTSTRAP_DATABASE_MUTATION`

## T1 cron dokaz

Dok su lockovi još preuzeti, bez drugog WordPress bootstrapa ponovi isti sanitizovani DB pregled.

Obavezno:

- sva tri dozvoljena hooka imaju broj `0`;
- kanonski SHA-256 cron stanja bez ta tri hooka jednak je T0 vrednosti;
- pad ukupnog broja događaja odgovara isključivo T0 broju tri dozvoljena hooka;
- nijedan drugi događaj nije dodat, uklonjen ili promenjen.

Svaki drugi drift je:

`FAIL: UNEXPECTED_CRON_STATE_DRIFT`

Ne pokušavaj fix-forward.

## Transport računovodstvo

Izveštaj mora odvojiti:

- approved internal staging DB transport: `APPROVED`;
- DB configuration values read/displayed: `0`;
- object-cache direct transport attempts;
- WP HTTP API attempts;
- guard-intercepted before transport;
- approved external transport attempts;
- actual external network requests izvan odobrene staging DB konekcije;
- unexpected external network requests.

PASS zahteva:

- object-cache direct transport attempts: `0`;
- approved external transport attempts: `0`;
- actual external network requests izvan odobrene staging DB konekcije: `0`;
- unexpected external network requests: `0`;
- real mail: `0`.

Normalna staging DB konekcija ne računa se kao spoljni ili neočekivani transport u okviru ovog ugovora.

## Cleanup

Uvek:

1. oslobodi runtime lock;
2. oslobodi cron-runner lock;
3. ukloni samo sopstveni task-private direktorijum;
4. potvrdi da je uklonjen;
5. potvrdi nepromenjen staging marker;
6. potvrdi nepromenjene Git reference;
7. potvrdi deploy/snapshot/restore: `NO`;
8. potvrdi production access: `0`.

## PASS kriterijumi

PASS samo ako:

- environment je tačno `staging`;
- sve tri runtime zaštite su potvrđene;
- persistent object-cache transport je onemogućen pre bootstrapa;
- uklonjeni su samo dozvoljeni DROP hookovi;
- T0/T1 hash ostatka cron stanja je isti;
- nema drugog DB upisa;
- nema scheduler/action/mail/payment izvršavanja;
- nema spoljnog transporta izvan unapred odobrene staging DB konekcije;
- cleanup je potpun;
- produkcija nije pristupana.

## Sledeći zadatak — samo definisati

Samo ako 2.69.2 prođe, preporuči:

`Zadatak 2.70 — Immutable snapshot i kontrolisani combined staging deploy`

Ne izvršavaj ga sada.

## Izveštaj

Objavi tačno jedan izveštaj:

`Zadatak 2.69.2 — Potvrdi staging zaštite bez čitanja DB konfiguracije`

Uključi T0/T1 dokaze, runtime simbole/hookove, SQL kategorije, transport računovodstvo, lockove, cleanup, nepromenjene SHA/marker vrednosti i:

- Deploy: `NO`;
- Snapshot/restore: `NO`;
- DB config values read/displayed: `0`;
- Production access: `0`;
- `Production touched: NO`.

Posle daljinske potvrde izveštaja — STOP.
