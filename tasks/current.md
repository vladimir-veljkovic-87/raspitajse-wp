# Zadatak 2.69 — Klasifikuj bootstrap housekeeping i potvrdi staging zaštite

Status: READY
Staging baseline: f6093c11d71ddc175ebf83fc69453c20aaca5239
Release candidate branch: feature/task-2.68.2-wp-7.1.2-release-preparation
Release candidate HEAD: 0c233ad646d7f8f0bac1079eb21f7e73b43d29b0
Previous task: 2.68.3 — PARTIAL
Production: FORBIDDEN
Risk class: AUTONOMOUS_SAFE
Mutation class: BOUNDED_TECHNICAL_HOUSEKEEPING
Deployment: FORBIDDEN

## Cilj

Ponovi kontrolisanu staging runtime proveru iz Zadatka 2.68.3, uz eksplicitnu dozvolu za precizno klasifikovano uklanjanje najviše tri DROP telemetry/reporting WP-Cron hooka:

- `elementor/tracker/send_event`;
- `monsterinsights_feature_feedback_checkin`;
- `wpforms_email_summaries_cron`.

Potvrdi runtime aktivaciju staging mail-safety, cron-recovery i Action Scheduler drop/async-dispatch zaštita.

Obavezni uspešan rezultat:

`PASS: STAGING_RUNTIME_PROTECTIONS_ATTESTED`

Ovaj zadatak ne deployuje release candidate, ne spaja grane, ne pravi snapshot i ne pristupa produkciji.

## Obavezna početna provera

Pročitaj u celosti:

- `AGENTS.md`;
- `CODEX_WORKFLOW.md`;
- `CODEX_HOST_RESILIENCE.md`;
- `STAGING_MUTATION_POLICY.md`;
- `STAGING_DEFINITION_OF_DONE.md`;
- najnoviji izveštaj Zadatka 2.68.3.

Potvrdi:

- `origin/staging` je `f6093c11d71ddc175ebf83fc69453c20aaca5239`;
- remote release candidate je `0c233ad646d7f8f0bac1079eb21f7e73b43d29b0`;
- staging marker sadrži baseline;
- staging realpath je tačno `/home/u601262303/domains/raspitajse.com/public_html/public_html_stage`;
- working tree je čist;
- nema konkurentnog Git, rsync, deploy, snapshot, restore, core-update ili queue-runner procesa.

Pri pregledu procesa ne prikazuj pune argumente koji mogu sadržati tajne. Koristi samo PID, naziv procesa, stanje i trajanje.

## Eksplicitno dozvoljene mutacije

Tokom jednog WordPress bootstrapa dozvoljeno je isključivo:

- uklanjanje postojećih instanci tri navedena WP-Cron hooka;
- odgovarajući strogo ograničeni update WordPress `cron` optiona;
- cache invalidacija/populacija neposredno izazvana tim updateom ili read-only bootstrapom;
- privremeni task fajlovi samo pod `/tmp/raspitajse-task-2.69-*`;
- preuzimanje i oslobađanje propisanih lockova.

Ova tri hooka su klasifikovana kao `DROP` tehnički reporting/telemetry poslovi bez poslovnog značenja.

Nije dozvoljeno:

- uklanjanje ili promena bilo kog drugog cron događaja;
- izvršavanje cron callbacka ili Action Scheduler akcije;
- pokretanje WP-Crona ili queue runnera;
- poslovni, korisnički, content, payment, application, message ili order upis;
- mail ili stvarni spoljni HTTP transport;
- promena staging fajlova ili deploy markera.

## T0 cron dokaz bez WordPress bootstrapa

Pre WordPress bootstrapa, koristeći staging-scoped WP-CLI DB alat koji ne učitava WordPress plugine:

1. pročitaj samo `cron` option iz staging baze;
2. ne prikazuj sirovi ili serijalizovani sadržaj;
3. analiziraj ga task-private PHP helperom;
4. zabeleži samo:
   - broj instanci svakog od tri dozvoljena hooka;
   - ukupan broj cron događaja;
   - SHA-256 kanonskog cron stanja nakon logičkog uklanjanja samo ta tri hooka.

Helper ne sme prikazivati argumente cron događaja, korisničke podatke ili option sadržaj.

Ako se cron option ne može bezbedno analizirati bez prikazivanja sadržaja, zaustavi se pre bootstrapa sa `PARTIAL`.

## Privremeni preload guard

U novom regularnom non-symlink direktorijumu `/tmp/raspitajse-task-2.69-<collision-resistant-id>` napravi minimalan WP-CLI preload guard koji pre WordPress učitavanja postavlja, ako već nisu postavljene:

- `DISABLE_WP_CRON=true`;
- `AUTOMATIC_UPDATER_DISABLED=true`;
- `WP_AUTO_UPDATE_CORE=false`;
- `WP_HTTP_BLOCK_EXTERNAL=true`;
- `SAVEQUERIES=true`.

Guard mora:

- proći `php -l`;
- biti pregledan i SHA-256 zabeležen;
- ne čitati niti prikazivati konfiguraciju;
- ne registrovati događaje;
- ne slati mail;
- ne praviti mrežni zahtev;
- ne menjati bazu.

Proveri da WP-CLI `--require` učitava guard pre WordPress bootstrapa. Ako to nije dokazivo, STOP pre bootstrapa.

## Lockovi

Nonblocking preuzmi:

1. `/tmp/raspitajse-staging-owned-cron-runner.lock`;
2. `/tmp/raspitajse-staging-runtime.lock`.

Ako neki lock nije odmah dostupan, oslobodi prethodni i objavi `PARTIAL: STAGING_RUNTIME_LOCK_BUSY`.

Lockove oslobodi obrnutim redom.

## Jedna runtime proba

Pokreni tačno jedan staging-scoped WP-CLI bootstrap sa preload guardom, `--skip-plugins` i `--skip-themes`, bez cron ili Action Scheduler izvršavanja.

MU plugini i drop-in komponente moraju ostati učitani.

Bezbedno prikaži samo:

- `wp_get_environment_type()`;
- relevantne učitane MU-plugin basenames;
- očekivane `function_exists` rezultate;
- očekivane `has_action`/`has_filter` rezultate i prioritete;
- vrednosti zaštitnih boolean konstanti;
- agregirane SQL brojače.

Potvrdi najmanje:

### Mail safety

- `raspitajse_staging_mail_safety_is_staging()` postoji i vraća aktivno stanje;
- `pre_wp_mail`, `wp_mail` i `phpmailer_init` zaštitni callbackovi su registrovani na očekivanim prioritetima.

### Cron recovery

- `raspitajse_staging_cron_recovery_guard_is_active()` postoji i vraća aktivno stanje;
- `DISABLE_WP_CRON === true`;
- telemetry/reporting disable filteri i callbackovi su registrovani.

### Action Scheduler

- `raspitajse_staging_disable_action_scheduler_async_dispatch` je registrovan;
- svi postojeći `pre_as_*` DROP filteri su registrovani na očekivanim prioritetima;
- nije pokrenut queue runner niti bilo koja Action Scheduler akcija.

## SQL klasifikacija

Interno pregledaj `SAVEQUERIES`, ali nikada ne prikazuj SQL tekst.

Dozvoljeni mutating SQL je samo update staging `cron` optiona koji odgovara uklanjanju dozvoljenih hookova.

Svaki drugi mutating ili neklasifikovan SQL je hard stop:

`FAIL: UNEXPECTED_BOOTSTRAP_DATABASE_MUTATION`

## T1 cron dokaz

Posle bootstrapa, dok su lockovi još preuzeti, ponovi isti sanitizovani DB pregled bez dodatnog WordPress bootstrapa.

Obavezno:

- sva tri dozvoljena hooka imaju broj `0`;
- kanonski SHA-256 cron stanja bez ta tri hooka jednak je T0 vrednosti;
- ukupan pad događaja odgovara isključivo T0 broju instanci ta tri hooka;
- nijedan drugi cron događaj nije dodat, uklonjen ili promenjen.

Ako se promenio bilo koji drugi događaj, objavi `FAIL: UNEXPECTED_CRON_STATE_DRIFT` i ne pokušavaj fix-forward.

## Transport i spoljašnji efekti

Dozvoljeni su WP HTTP API pokušaji samo ako su deterministički blokirani pre transporta.

Obavezno:

- approved external transport attempts: `0`;
- actual external network requests: `0`;
- unexpected external network requests: `0`;
- real mail: `0`;
- scheduler callback executions: `0`;
- Action Scheduler executions: `0`;
- payment operations: `0`.

Ako stvarni transport ne može nedvosmisleno da se utvrdi, rezultat je `PARTIAL: EXTERNAL_TRANSPORT_EVIDENCE_AMBIGUOUS`.

## Cleanup

Uvek:

1. oslobodi runtime lock;
2. oslobodi cron-runner lock;
3. ukloni samo sopstveni `/tmp/raspitajse-task-2.69-*`;
4. potvrdi da je uklonjen;
5. potvrdi da su staging marker i obe Git reference nepromenjeni;
6. potvrdi da nije bilo deploya, snapshota ili restorea.

Produkcijska putanja i produkcijska baza ne smeju biti čitane niti menjane.

## PASS kriterijumi

Zadatak je PASS samo ako:

- runtime environment je tačno `staging`;
- sve tri zaštite su aktivne;
- uklonjeni su isključivo dozvoljeni DROP hookovi;
- T0/T1 kanonski hash ostatka cron stanja je isti;
- nema drugog DB upisa;
- nema scheduler/action izvršavanja;
- nema realnog maila ili spoljnog transporta;
- marker i Git stanje su nepromenjeni;
- svi privremeni fajlovi i lockovi su očišćeni;
- produkcija nije pristupana.

## Sledeći zadatak — samo definisati

Ako 2.69 prođe, preporuči:

`Zadatak 2.70 — Immutable snapshot i kontrolisani combined staging deploy`

Nemoj ga izvršavati u ovom zadatku.

## Izveštaj

Objavi jedan izveštaj:

`Zadatak 2.69 — Klasifikuj bootstrap housekeeping i potvrdi staging zaštite`

Izveštaj mora sadržati:

- T0/T1 broj dozvoljenih hookova;
- T0/T1 kanonski hash bez njihovog sadržaja;
- agregirane SQL kategorije;
- runtime zaštitne simbole i hookove;
- HTTP/transport brojače;
- lock redosled i oslobađanje;
- cleanup;
- deploy: `NO`;
- snapshot/restore: `NO`;
- production access: `0`;
- `Production touched: NO`.

Posle potvrde objavljenog izveštaja — STOP.
