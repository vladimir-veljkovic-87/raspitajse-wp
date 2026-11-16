# Zadatak 2.69.1 — Izoluj cache transport i potvrdi staging runtime zaštite

Status: READY
Staging baseline: f6093c11d71ddc175ebf83fc69453c20aaca5239
Release candidate branch: feature/task-2.68.2-wp-7.1.2-release-preparation
Release candidate HEAD: 0c233ad646d7f8f0bac1079eb21f7e73b43d29b0
Previous task: 2.69 — PARTIAL
Production: FORBIDDEN
Risk class: AUTONOMOUS_SAFE
Mutation class: BOUNDED_TECHNICAL_HOUSEKEEPING
Deployment: FORBIDDEN
Time budget: 45 minutes

## Cilj

Završi runtime attestation koju Zadatak 2.69 nije pokrenuo zato što Hostinger zabranjuje `strace`, a LiteSpeed object-cache može direktno da otvori Redis/Memcached socket.

Koristi dokazanu LiteSpeed fail-closed putanju `LITESPEED_OC_FAILURE` da pre WordPress učitavanja deterministički onemogući svaki Redis/Memcached connect/pconnect/addServer transport, zatim izvrši tačno jedan kontrolisani staging bootstrap i potvrdi:

1. staging mail-safety zaštitu;
2. cron-recovery zaštitu;
3. Action Scheduler drop/async-dispatch zaštitu;
4. da su uklonjena samo tri unapred klasifikovana DROP telemetry/reporting cron hooka;
5. da nije bilo spoljnog mrežnog transporta.

Obavezni uspešan rezultat:

`PASS: STAGING_RUNTIME_PROTECTIONS_ATTESTED_WITH_CACHE_TRANSPORT_DISABLED`

Ne deployuj, ne pravi snapshot, ne menjaj Git grane i ne pristupaj produkciji.

## Obavezni kontekst

Pročitaj u celosti:

- `AGENTS.md`;
- `CODEX_WORKFLOW.md`;
- `CODEX_HOST_RESILIENCE.md`;
- `STAGING_MUTATION_POLICY.md`;
- `STAGING_DEFINITION_OF_DONE.md`;
- izveštaje Zadatka 2.68.3 i 2.69;
- aktivne staging kopije:
  - `wp-content/object-cache.php`;
  - LiteSpeed `object.lib.php`;
  - LiteSpeed `object-cache.cls.php`;
  - LiteSpeed `object-cache-wp.cls.php`;
  - sve MU plugine učitane pri bootstrapu.

Nemoj koristiti `strace`, `ptrace`, namespace izolaciju niti ponavljati neuspešni tracer test iz 2.69.

## Preflight

Potvrdi bez WordPress bootstrapa:

- `origin/staging=f6093c11d71ddc175ebf83fc69453c20aaca5239`;
- release candidate remote HEAD je `0c233ad646d7f8f0bac1079eb21f7e73b43d29b0`;
- working tree je čist;
- staging marker je regularan non-symlink fajl sa tačnim baseline-om;
- staging realpath je tačno `/home/u601262303/domains/raspitajse.com/public_html/public_html_stage`;
- nema konkurentnog Git, rsync, deploy, snapshot, restore, core-update, WP-Cron ili queue-runner procesa.

Procesni dokaz sme sadržati samo PID, command name, state i elapsed time, bez punih argumenata.

Potvrdi SHA-256 svih MU plugina, `object-cache.php` drop-ina i LiteSpeed biblioteka relevantnih za transport. Ne prikazuj cache host, port, username, password ili konfiguracione vrednosti.

## Statički dokaz cache izolacije

Pre pravljenja guarda dokaži iz tačno učitanog LiteSpeed koda:

1. Redis `connect`/`pconnect` i Memcached `addServer` dostupni su samo kroz LiteSpeed `_connect()`;
2. `_connect()` vraća `false` pre tih poziva kada je `LITESPEED_OC_FAILURE` definisan;
3. `wp_cache_init()` pri tom stanju registruje prelazak na neeksterni cache režim;
4. nakon `muplugins_loaded`, `wp_using_ext_object_cache()` treba da bude `false`;
5. nijedan drugi učitani MU plugin ili drop-in nema direktan bootstrap-time `socket_connect`, `fsockopen`, `stream_socket_client`, cURL, Redis ili Memcached transport koji zaobilazi WordPress HTTP API i ovu LiteSpeed blokadu.

Ako bilo koja stavka nije dokazana, STOP pre bootstrapa sa:

`PARTIAL: DIRECT_TRANSPORT_ISOLATION_NOT_PROVEN`

## Eksplicitno dozvoljene mutacije

Dozvoljeno je samo:

- uklanjanje postojećih instanci:
  - `elementor/tracker/send_event`;
  - `monsterinsights_feature_feedback_checkin`;
  - `wpforms_email_summaries_cron`;
- odgovarajući update isključivo staging `cron` optiona;
- lokalni in-process cache i DB fallback neophodan za jedan bootstrap;
- task-private fajlovi pod `/tmp/raspitajse-task-2.69.1-*`;
- nonblocking preuzimanje propisanih lockova.

Nije dozvoljeno:

- menjanje drugog cron događaja;
- izvršavanje cron/Action Scheduler callbacka;
- spoljni Redis/Memcached/HTTP/SMTP transport;
- poslovni, korisnički, content, application, order ili payment upis;
- promena staging fajlova, plugina, drop-ina, konfiguracije ili markera.

## T0 cron dokaz bez WordPress bootstrapa

Koristeći staging-scoped WP-CLI DB alat koji ne učitava WordPress plugine pročitaj samo `cron` option i analiziraj ga task-private PHP helperom.

Ne prikazuj sirovi option, serijalizovani sadržaj, callback argumente ili privatne podatke.

Zabeleži samo:

- broj događaja za svaki od tri dozvoljena hooka;
- ukupan broj cron događaja;
- SHA-256 kanonskog cron stanja nakon logičkog uklanjanja samo tri dozvoljena hooka.

Interno potvrdi da staging DB transport koristi Unix socket, loopback ili isti odobreni Hostinger DB servis. Ne prikazuj DB host ili kredencijale. Ako se DB transport ne može klasifikovati bez otkrivanja tajni, STOP pre bootstrapa.

## Preload guard

U svežem regularnom non-symlink direktorijumu `/tmp/raspitajse-task-2.69.1-<id>` napravi minimalan PHP preload guard.

Pre WordPress učitavanja mora definisati, ako već nisu definisane:

- `LITESPEED_OC_FAILURE=true`;
- `DISABLE_WP_CRON=true`;
- `AUTOMATIC_UPDATER_DISABLED=true`;
- `WP_AUTO_UPDATE_CORE=false`;
- `WP_HTTP_BLOCK_EXTERNAL=true`;
- `SAVEQUERIES=true`.

Guard mora proći `php -l`, biti pregledan i imati zabeležen SHA-256. Ne sme čitati/štampati konfiguraciju, registrovati događaje, slati mail, otvarati transport ili menjati bazu.

Dokaži iz WP-CLI ponašanja/dokumentovanog redosleda da `--require` učitava guard pre WordPress bootstrapa. Ako nije dokazivo, STOP.

## Lock protokol

Nonblocking preuzmi redom:

1. `/tmp/raspitajse-staging-owned-cron-runner.lock`;
2. `/tmp/raspitajse-staging-runtime.lock`.

Ako neki nije odmah dostupan, oslobodi prethodni i objavi `PARTIAL: STAGING_RUNTIME_LOCK_BUSY`.

Oslobađanje je obrnutim redom.

## Jedina dozvoljena WordPress proba

Pokreni tačno jedan staging-scoped WP-CLI bootstrap sa:

- preload guardom;
- `--skip-plugins`;
- `--skip-themes`;
- bez cron ili Action Scheduler izvršavanja.

MU plugini i drop-in ostaju učitani.

Bezbedno izmeri i prikaži samo:

- environment type;
- relevantne MU-plugin basenames;
- zaštitne `function_exists`, `has_action` i `has_filter` rezultate/prioritete;
- boolean vrednosti guard konstanti;
- `wp_using_ext_object_cache()` kao boolean;
- agregirane SQL kategorije;
- agregirane transport kategorije.

Obavezno potvrdi:

### Cache/transport

- `LITESPEED_OC_FAILURE === true`;
- `wp_using_ext_object_cache() === false` posle `muplugins_loaded`;
- LiteSpeed `_connect()` nije mogao doći do Redis/Memcached transport grane;
- WordPress HTTP spoljni transport je blokiran sa `WP_HTTP_BLOCK_EXTERNAL`;
- nema drugog direktnog transport puta u učitanom scope-u.

### Mail safety

- `raspitajse_staging_mail_safety_is_staging()` postoji i vraća aktivno stanje;
- zaštite na `pre_wp_mail`, `wp_mail` i `phpmailer_init` registrovane su na očekivanim prioritetima.

### Cron recovery

- `raspitajse_staging_cron_recovery_guard_is_active()` postoji i vraća aktivno stanje;
- `DISABLE_WP_CRON === true`;
- očekivani telemetry/reporting disable callbackovi i filteri su registrovani.

### Action Scheduler

- `raspitajse_staging_disable_action_scheduler_async_dispatch` je registrovan;
- svi postojeći `pre_as_*` DROP filteri su registrovani;
- queue runner i Action Scheduler akcije nisu izvršeni.

## SQL klasifikacija

Interno pregledaj `SAVEQUERIES`, ali ne prikazuj SQL.

Jedini dozvoljeni mutating SQL je update staging `cron` optiona potreban da ukloni T0 instance tri dozvoljena hooka.

Svaki drugi mutating ili neklasifikovan SQL je:

`FAIL: UNEXPECTED_BOOTSTRAP_DATABASE_MUTATION`

## T1 cron dokaz

Dok su lockovi još preuzeti, bez drugog WordPress bootstrapa ponovi sanitizovani DB pregled.

Obavezno:

- sva tri dozvoljena hooka imaju broj `0`;
- kanonski SHA-256 cron stanja bez ta tri hooka jednak je T0 vrednosti;
- pad ukupnog broja događaja jednak je T0 broju samo ta tri hooka;
- nijedan drugi događaj nije dodat, uklonjen ili promenjen.

Svaki drugi drift je:

`FAIL: UNEXPECTED_CRON_STATE_DRIFT`

Ne pokušavaj fix-forward.

## Transport rezultat

Izveštaj mora odvojiti:

- approved internal staging DB transport;
- object-cache direct transport attempts;
- WP HTTP API attempts;
- guard-intercepted before transport;
- approved external transport attempts;
- actual external network requests;
- unexpected external network requests.

PASS zahteva:

- object-cache direct transport attempts: `0`;
- approved external transport attempts: `0`;
- actual external network requests: `0`;
- unexpected external network requests: `0`;
- real mail: `0`.

Nemoj vrednost `not measured` pretvarati u nulu. Ako neki preostali mogući spoljni transport nije deterministički blokiran ili statički isključen, rezultat ostaje `PARTIAL`.

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
- persistent object-cache transport je deterministički onemogućen pre bootstrapa;
- uklonjeni su samo dozvoljeni DROP hookovi;
- T0/T1 hash ostatka cron stanja je isti;
- nema drugog DB upisa;
- nema scheduler/action/mail/payment izvršavanja;
- nema spoljnog transporta;
- cleanup je potpun;
- produkcija nije pristupana.

## Sledeći zadatak — samo definisati

Samo ako 2.69.1 prođe, preporuči:

`Zadatak 2.70 — Immutable snapshot i kontrolisani combined staging deploy`

Ne izvršavaj ga sada.

## Izveštaj

Objavi tačno jedan izveštaj:

`Zadatak 2.69.1 — Izoluj cache transport i potvrdi staging runtime zaštite`

Uključi T0/T1 dokaze, runtime simbole/hookove, SQL kategorije, cache/HTTP transport klasifikaciju, lockove, cleanup, nepromenjene SHA/marker vrednosti i:

- Deploy: `NO`;
- Snapshot/restore: `NO`;
- Production access: `0`;
- `Production touched: NO`.

Posle daljinske potvrde izveštaja — STOP.
