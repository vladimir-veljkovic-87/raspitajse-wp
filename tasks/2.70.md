# Zadatak 2.70 — Kompletan transakcioni rollback paket za combined staging release

Status: READY
Staging baseline: f6093c11d71ddc175ebf83fc69453c20aaca5239
Accepted release base: feature/task-2.68.2-wp-7.1.2-release-preparation
Accepted release HEAD: 0c233ad646d7f8f0bac1079eb21f7e73b43d29b0
Previous task: 2.69.2 — PASS
Production: FORBIDDEN
Risk class: AUTONOMOUS_SAFE
Mutation class: REPOSITORY_ONLY_RELEASE_SAFETY
Deployment: FORBIDDEN
Time budget: 60 minutes

## Cilj

Pre stvarnog staging deploya zatvori dokazanu rollback prazninu u `deployment/deploy-staging.sh`.

Postojeći `snapshot-packages`/`restore-packages` čuva samo:

- `wp-admin`;
- `wp-includes`;
- `wp-content/plugins/wpforms-lite`;
- 15 kanonskih WordPress root fajlova.

Combined release menja i Raspitajse-owned Communications, Commerce i MU-plugin fajlove, kao i deploy marker/Communications manifest. Sam package snapshot zato nije dovoljan da posle neuspelog post-deploy gate-a vrati kompletno pre-deploy stanje.

Implementiraj i offline dokaži jedan immutable, commit-pair-keyed, atomski transakcioni release snapshot/restore mehanizam koji može byte-exact da vrati sve što combined `changed` deploy može promeniti.

Obavezni uspešan rezultat:

`PASS: COMPLETE_TRANSACTIONAL_STAGING_ROLLBACK_READY`

Ovaj zadatak je repository-only. Ne pravi server snapshot, ne deployuje, ne menja staging runtime, bazu, cache, marker ili produkciju.

## Obavezni kontekst

Pročitaj u celosti:

- `AGENTS.md`;
- `CODEX_WORKFLOW.md`;
- `CODEX_HOST_RESILIENCE.md`;
- `STAGING_MUTATION_POLICY.md`;
- `STAGING_DEFINITION_OF_DONE.md`;
- izveštaj 2.68.2;
- izveštaj 2.69.2;
- aktuelni `deployment/deploy-staging.sh`;
- postojeći immutable package snapshot harness.

Potvrdi pre rada:

- `origin/staging=f6093c11d71ddc175ebf83fc69453c20aaca5239`;
- remote accepted release HEAD je `0c233ad646d7f8f0bac1079eb21f7e73b43d29b0`;
- accepted release je 19 commitova ispred i 0 iza baseline-a;
- working tree je čist;
- tačan delta accepted release-a prema baseline-u sadrži samo već prihvaćena 22 fajla;
- najnoviji report za 2.69.2 je PASS.

## Grana

Kreiraj novu granu:

`feature/task-2.70-transactional-staging-rollback`

tačno iz:

`0c233ad646d7f8f0bac1079eb21f7e73b43d29b0`

Ako grana već postoji lokalno ili remote, ne resetuj je i ne prepisuj. Pregledaj stanje i zaustavi se na neočekivanom sadržaju.

## Dozvoljeni repository scope

U odnosu na accepted release HEAD smeju se menjati samo:

- `deployment/deploy-staging.sh`;
- novi/fokusirani harness fajlovi pod `deployment/tests/`.

Ne menjaj:

- WordPress core;
- product kod iz 2.63–2.67;
- Communications/Commerce/MU runtime kod;
- temu ili vendor plugin;
- `.gitattributes`;
- konfiguraciju, tajne ili produkcijske putanje.

## Novi interfejs

Dodaj jasno imenovane modove, na primer:

- `snapshot-release`;
- `restore-release`.

Nazivi mogu odstupati samo ako su dosledni, eksplicitni i dokumentovani u usage izlazu.

Transakcioni snapshot mora biti vezan za tačan par:

- PREVIOUS_SHA iz postojećeg staging marker fajla;
- HEAD_SHA tačno checkoutovanog release commita.

Finalni snapshot ključ mora biti deterministički i sadržati oba puna SHA-a. Latest/first/glob izbor je zabranjen.

Koristi novi versioned root pod:

`/home/u601262303/deploy-state`

Ne menjaj, preimenuj, rotiraj ili briši:

- legacy rollback direktorijum;
- postojeći package snapshot v2 root;
- ranije immutable snapshotove.

## Potpun mutation plan

Pre snapshotovanja generiši deterministički plan svih runtime/deploy-state putanja koje odgovarajući `changed` deploy može promeniti.

Plan mora pokriti:

1. kompletne package tree rootove i 15 core root fajlova kada je bilo koji package path promenjen;
2. svaki changed/deleted Raspitajse-owned allowlisted fajl;
3. cele vendor reconciliation rootove samo ako ih konkretan commit diff zaista dodiruje;
4. svaki retired-plugin root samo ako ga konkretan deploy planira ukloniti;
5. staging commit marker;
6. Communications manifest;
7. sve druge deploy-state fajlove koje isti uspešan deploy ažurira.

Aktuelni `changed_deploy` trenutno bezuslovno reconciliše tri vendor root-a. Ispravi ga tako da vendor root bude reconciliovan samo kada tačan PREVIOUS_SHA..HEAD_SHA diff dodiruje taj root, ili uključi byte-exact kompletan root u transakcioni snapshot. Preferiraj uži uslovni deploy kada ponašanje ostaje ispravno i testirano.

Nijedan runtime path van deklarisanog plana ne sme biti menjan ni snapshotovan.

## Snapshot format i bezbednost

Snapshot mora:

- biti izgrađen u same-parent task-private privremenom direktorijumu;
- biti objavljen atomskim rename-om;
- koristiti publication `flock`;
- odbiti postojeći finalni snapshot bez menjanja;
- odbiti symlink za root, metadata, manifest ili payload putanju;
- sadržati format verziju, PREVIOUS_SHA, HEAD_SHA i aggregate manifest SHA-256;
- evidentirati i postojeće i odsutne putanje;
- sačuvati regularne fajlove byte-exact;
- sačuvati potreban directory inventory za bezbedan restore;
- imati deterministički sortirani manifest;
- biti potpuno verifikovan pre publication rename-a;
- nemati latest/glob fallback;
- nemati automatsko pruning/rotation ponašanje.

Failure cleanup sme obrisati samo privremeni direktorijum koji je napravila ta operacija.

## Restore semantika

`restore-release` mora pre bilo kog target upisa:

- pronaći samo exact PREVIOUS_SHA/HEAD_SHA snapshot;
- proveriti safe types i zabranu symlinkova;
- proveriti metadata i kompletan manifest;
- proveriti da se trenutni checkout/target transakcija odnosi na očekivani HEAD_SHA;
- izgraditi kompletan restore plan;
- odbiti missing, extra, changed ili unsafe snapshot sadržaj.

Restore mora:

1. vratiti sve prethodno postojeće fajlove byte-exact;
2. ukloniti samo ledger putanje koje su pre deploya bile odsutne, a deploy ih je kreirao;
3. vratiti kompletne package rootove uključujući uklanjanje post-snapshot extra fajlova;
4. vratiti relevantne vendor/retired-plugin rootove samo kada su bili deo plana;
5. vratiti Communications manifest i commit marker tek nakon uspešnog content restorea;
6. ostaviti marker netaknut ako content restore ne uspe;
7. nikada ne dirati putanju koja nije u verifikovanom snapshot ledgeru;
8. potvrditi finalni runtime/deploy-state aggregate hash jednak pre-deploy hashu.

Restore ne sme koristiti Git baseline kao zamenu za runtime prestate. Snapshot runtime bajtovi su autoritativni jer je pre-deploy staging core već WordPress 7.1.2 i ne mora biti byte-identičan starom Git baseline-u.

## Marker i communications konzistentnost

Posle uspešnog restorea:

- commit marker mora biti vraćen na tačan PREVIOUS_SHA iz snapshota;
- Communications manifest mora biti byte-exact vraćen u pre-deploy stanje;
- runtime Communications tree mora odgovarati snapshot prestate-u;
- package/core runtime mora odgovarati snapshot prestate-u;
- ne sme ostati mešano stanje release koda i baseline markera.

Ako pre snapshotovanja marker ili manifest nedostaje, to odsustvo mora biti evidentirano i restore mora vratiti odsustvo, bez izmišljanja sadržaja.

## Offline harness

Proširi postojeći harness ili dodaj novi fokusirani:

`deployment/tests/transactional-release-rollback-harness.sh`

Koristi isključivo task-private lokalne fixture direktorijume. Nikada ne pristupaj realnom staging ili deploy-state stablu.

Harness pokreni najmanje dva puta iz čistih fixture rootova i dokaži:

1. exact PREVIOUS_SHA/HEAD_SHA ključ;
2. kompletan deterministički mutation plan;
3. package root snapshot i restore;
4. changed owned file restore;
5. newly-created file deletion pri restoreu;
6. pre-existing deleted file byte-exact povratak;
7. marker i manifest byte-exact povratak;
8. pre-existing runtime drift vraćen iz snapshota, ne iz Git baseline-a;
9. vendor root nije dotaknut kada diff ne dodiruje vendor root;
10. vendor root jeste potpuno zaštićen kada plan uključuje reconciliation;
11. retired-plugin root odsutan/postojeći lifecycle;
12. duplicate snapshot rejection bez promene prvog;
13. dva različita commit para koegzistiraju;
14. paralelno publication takmičenje: tačno jedan uspeh;
15. wrong PREVIOUS_SHA ili HEAD_SHA rejection;
16. changed/missing/extra manifest entry rejection pre target mutacije;
17. symlinked root/payload/metadata rejection;
18. failure injection pre publication čisti samo sopstveni temp;
19. failure injection tokom restorea ne ažurira marker/manifest;
20. full successful restore daje byte-identičan aggregate prestate hash;
21. unrelated runtime i deploy-state sentineli ostaju byte-identični;
22. legacy i package-v2 snapshot fixture sentineli ostaju netaknuti.

## Regresije i statičke provere

Obavezno:

- `bash -n` za svaki promenjeni shell fajl;
- `shellcheck` samo ako je već instaliran;
- `git diff --check`;
- postojeći immutable package snapshot harness dva puta;
- novi transactional release rollback harness dva puta;
- postojeći 2.67 stacked product journey harness dva puta;
- svi fokusirani 2.63–2.66 product harnessi;
- PHP lint svih cumulative product PHP fajlova;
- potvrda da su svih 22 accepted release fajla van `deployment/` byte-identična accepted release HEAD-u;
- fresh checkout test finalnog commita;
- provera da nema produkcijske putanje, tajne, broad unresolved delete, latest/glob snapshot izbora ili implicit legacy fallbacka.

Testove je dozvoljeno pokrenuti u lokalnom offline Docker kontejneru sa mrežom isključenom nakon pripreme zavisnosti.

## Commit i push

Tek kada svi gate-ovi prođu:

- napravi fokusiran commit;
- pushuj samo `feature/task-2.70-transactional-staging-rollback`;
- bez force-pusha;
- potvrdi remote HEAD jednak lokalnom;
- potvrdi accepted release HEAD je direktni predak;
- potvrdi finalni delta prema accepted release-u sadrži samo dozvoljeni deployment scope.

Ne menjaj niti mergeuj:

- `staging`;
- prethodnu release granu;
- `main`;
- baseline grane.

## Nulta runtime mutacija

Ovaj zadatak mora završiti sa:

- real server snapshot invocations: `0`;
- restore invocations: `0`;
- deploy invocations: `0`;
- staging filesystem changes: `0`;
- staging DB/cache changes: `0`;
- scheduler/mail/payment/browser actions: `0`;
- production access: `0`.

## Sledeći zadatak — samo definisati

Ako 2.70 prođe, preporuči:

`Zadatak 2.71 — Immutable transactional snapshot i combined staging deploy`

2.71 mora:

- početi od tačnog remote 2.70 HEAD-a;
- ponovo potvrditi runtime zaštite;
- preuzeti lockove redom cron-runner pa runtime;
- napraviti i potpuno verifikovati exact commit-pair transakcioni snapshot;
- izvršiti tačno jedan `changed` deploy kompletnog release-a;
- pokrenuti samo server-side post-deploy gate-ove;
- na bilo kom neuspehu pokrenuti exact `restore-release`;
- dokazati byte-exact prestate, marker i manifest posle rollbacka;
- ne pokretati browser ili fixture journey u istom zadatku;
- ne pristupati produkciji.

Ne izvršavaj 2.71 sada.

## Izveštaj

Objavi tačno jedan izveštaj:

`Zadatak 2.70 — Kompletan transakcioni rollback paket za combined staging release`

Izveštaj mora sadržati:

- granu i tačan HEAD;
- changed files;
- mutation-plan pravila;
- snapshot format/root/key;
- restore semantiku;
- rezultate oba prolaza oba snapshot harnessa;
- failure/concurrency dokaze;
- kompletne regresije;
- remote branch potvrdu;
- nulta runtime mutacija;
- Deploy: `NO`;
- Production access: `0`;
- `Production touched: NO`.

Posle daljinske potvrde izveštaja — STOP.
