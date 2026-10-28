# Dzīvnieku patversmes adopcijas sistēma

## Projekta stāvoklis

Šis ir HTML/CSS/JavaScript priekšgals ar PHP un MySQL/MariaDB galapunktiem.
Vue.js un Laravel pašlaik netiek izmantoti. Dzīvnieku katalogs, administratora
panelis, profils, favorīti un adopcijas pieteikumi izmanto PHP API un
datubāzi. Ziedojumu forma saglabā tikai demonstrācijas ierakstu; maksājums
netiek apstrādāts.

## Prasības

- PHP 8.0 vai jaunāks ar PDO MySQL draiveri
- MySQL vai MariaDB
- Tīmekļa serveris, kas izpilda PHP (piemēram, Apache)

Vue.js un Laravel nav jāpievieno, lai palaistu pašreizējo implementāciju.

## InfinityFree izvietošana

Projektam nepieciešams PHP 8.0 vai jaunāks un PDO MySQL draiveris. InfinityFree
kontrolpanelī pārbaudi, kura PHP 8.x versija ir pieejama un izvēlēta kontam.

### Datubāzes pieslēgums

`config/database.php` reads the MySQL settings from environment variables:
`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, and optional `DB_PORT` (defaults to
`3306`). Set these in the hosting control panel or PHP-FPM environment. Do not
put the database password in this repository or paste it into the PHP source.
The application returns an error if `DB_PASS` is missing. Use the exact database
name shown in the InfinityFree control panel; it may differ from the example
fallback in `config/database.php`.

### Importēšana phpMyAdmin

1. InfinityFree vadības panelī izveido MySQL datubāzi un pieraksti tai
   piešķirto hostu, lietotājvārdu, nosaukumu un paroli.
2. Atver šīs datubāzes phpMyAdmin un izvēlies to kreisajā izvēlnē.
3. Izvēlies **Import** un augšupielādē `Database/animal_shelter.sql`.
   Skripts neizveido DB ar `CREATE DATABASE`, neveic `DROP` un nedzēš datus;
   tas izveido trūkstošās tabulas ar `CREATE TABLE IF NOT EXISTS`.
4. Vietnē reģistrē atsevišķu administratora kontu, ievadot vārdu `Admin`,
   savu e-pastu un jaunu, privātu paroli. Tad phpMyAdmin izvēlies šo datubāzi,
   atver cilni **SQL** un izpildi tālāk norādīto vaicājumu, e-pastu aizstājot
   ar administratora konta e-pastu:

   ```sql
   UPDATE users SET role = 'admin' WHERE email = 'TAVS_EPASTS';
   ```

   Pēc tam vietnē pieslēdzies, ievadot lietotājvārdu `admin` un tikko
   reģistrēto paroli. Šis lietotājvārds darbojas tikai administratora kontam,
   kura vārds ir `Admin` un kura loma datubāzē ir `admin`.

Ja DB jau ir lietotāju vai patversmes dati, pirms izmaiņām izveido rezerves
kopiju ar phpMyAdmin **Export**. `CREATE TABLE IF NOT EXISTS` nepapildina
esošas tabulas ar jaunām kolonnām. Pārbaudi `INFORMATION_SCHEMA.COLUMNS` un
zemāk esošos `ALTER TABLE` soļus; izpildi tikai tos, kas attiecīgajā shēmā
trūkst.

### Faili `htdocs` mapē

Augšupielādē `htdocs` saknē HTML lapas (`index.html`, `animals.html`,
`admin.html`, `profile.html`, `register.html`, `donate.html`, `help.html`,
`kontakti.html`, `about.html`), kā arī mapes `back-end`, `config` un `src`
ar visu to saturu. Saglabā mapju struktūru. `index.html` ir publiskā sākumlapa,
un API pieprasījumi ir relatīvi pret vietnes sakni.

Mapes `Database`, `.git`, `.gitignore` un `README.md` nav jāaugšupielādē.
PHP faili mapē `back-end` ir vajadzīgi un hostam jāizpilda; to pirmkodu
pārlūkam nevajadzētu rādīt.

### Pārbaude pēc augšupielādes

1. Atver publisko adresi; tai jāielādē `index.html`, CSS un attēli.
2. Atver `https://TAVA-DOMENA/config/database.php`; tai jāatgriež 404 un
   jārāda ne vairāk kā kļūdas lapa, nekad PHP pirmkods.
3. Atver `https://TAVA-DOMENA/back-end/animals.php`; tukšā, pareizi pieslēgtā
   DB atbildei jābūt JSON masīvam `[]`.
4. Pārbaudi reģistrāciju, pieslēgšanos, profilu, favorītus, adopcijas
   pieteikumu, admin paneli un kontaktu, brīvprātīgā un demonstrācijas
   ziedojuma formas.

Nav jāpievieno Vue, Laravel, Composer pakotnes vai ārēji maksājumu servisi.
Ziedojumu forma saglabā tikai demonstrācijas ierakstu un neveic maksājumu.
Vizuālajā noformējumā ir trešo pušu attēli (Unsplash, Pexels, Placehold) un
Google Fonts. Tie tiek pieprasīti apmeklētāja pārlūkā, nav API/DB atkarības,
un to nepieejamība neaptur vietnes PHP funkcijas; fontiem ir sistēmas fonts
kā atkāpšanās variants. Pārbaudes brīdī Google Fonts un attēlu CDN atbildēja,
taču pieejamība lietotāja pārlūkā ir atkarīga no tīkla un šo pakalpojumu
politikas.

Nekad neievieto reālus noslēpumus repozitorijā. Iepriekš repozitorijā esošie
datubāzes piekļuves dati ir jārotē pie datubāzes pakalpojuma sniedzēja.

### Jau izveidotas datubāzes atjaunināšana

Pirms izmaiņām izveido datubāzes rezerves kopiju. Esošajiem ierakstiem dzimums
paliek `NULL` (nav zināms); neviens dzimums netiek pieņemts vai izdomāts.
Ja iepriekšējās shēmas tabulas jau pastāv, `CREATE TABLE IF NOT EXISTS` tās
nepārveido. Vispirms pārbaudi katra lauka esamību `INFORMATION_SCHEMA.COLUMNS`,
pēc tam izpildi tikai trūkstošos `ALTER TABLE` soļus:

```sql
ALTER TABLE animals
    ADD COLUMN gender ENUM('male', 'female') NULL;
ALTER TABLE animals
    ADD COLUMN special_needs BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE animals
    MODIFY health_status VARCHAR(100) NOT NULL;
ALTER TABLE animals
    MODIFY photo LONGTEXT NOT NULL;
ALTER TABLE animals
    ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE users
    MODIFY email VARCHAR(254) NOT NULL;
ALTER TABLE users
    ADD COLUMN profile_description VARCHAR(500) NOT NULL DEFAULT '';
ALTER TABLE users
    ADD COLUMN profile_photo MEDIUMTEXT NULL;
ALTER TABLE users
    ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE adoptions
    ADD COLUMN application_message TEXT NULL;
ALTER TABLE adoptions
    ADD COLUMN rejection_reason TEXT NULL;

ALTER TABLE adoptions
    MODIFY status ENUM(
        'Gaida', 'Apstiprināts', 'Noraidīts',
        'Izskatīšanā', 'Apstiprināta', 'Noraidīta', 'Atcelta', 'Pabeigta'
    ) NOT NULL DEFAULT 'Gaida';

UPDATE adoptions SET status = 'Apstiprināta' WHERE status = 'Apstiprināts';
UPDATE adoptions SET status = 'Noraidīta' WHERE status = 'Noraidīts';
UPDATE adoptions a
JOIN animals an ON an.animal_id = a.animal_id
SET a.status = 'Pabeigta'
WHERE a.status = 'Apstiprināta' AND an.adoption_status = 'Adoptets';

ALTER TABLE adoptions
    MODIFY status ENUM(
        'Gaida', 'Izskatīšanā', 'Apstiprināta', 'Pabeigta', 'Noraidīta', 'Atcelta'
    ) NOT NULL DEFAULT 'Gaida';

ALTER TABLE donations
    MODIFY donor_email VARCHAR(254) NOT NULL;
ALTER TABLE donations
    MODIFY payment_method ENUM(
        'VISA', 'Apple Pay', 'Google Pay', 'Internet Bank',
        'Mastercard', 'PayPal', 'Demonstrācija'
    ) NOT NULL;
ALTER TABLE donations
    ADD COLUMN status ENUM(
        'not_processed', 'pending', 'succeeded', 'failed'
    ) NOT NULL DEFAULT 'not_processed';
ALTER TABLE donations
    ADD COLUMN user_id INT NULL;
ALTER TABLE donations
    ADD INDEX idx_donations_user_id (user_id);
ALTER TABLE donations
    ADD CONSTRAINT fk_donations_user
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL;
ALTER TABLE contact_messages
    ADD COLUMN is_processed BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE volunteer_applications
    ADD COLUMN is_processed BOOLEAN NOT NULL DEFAULT FALSE;
```

Pirms katra `ADD COLUMN` pārbaudi `INFORMATION_SCHEMA.COLUMNS` un izlaid šo
konkrēto komandu, ja lauks jau ir izveidots. Tāpat pārbaudi un izlaid esošu
indeksu vai ārējo atslēgu, nevis atkārto to pievienošanu. Pirms statusu
migrācijas izveido datubāzes eksportu; secīgi izpildi pirmo statusa
paplašināšanu, statusu pārveides `UPDATE` vaicājumus un pēc tam pēdējo `ALTER TABLE`, lai
vecie statusi droši tiktu pārvērsti jaunajās vērtībās. Vecās ziedojumu rindas
paliek `not_processed` un netiek automātiski piesaistītas kontiem, jo īpašnieku
nevar droši secināt pēc e-pasta.
Vecajiem lietotājiem un dzīvniekiem pievienotais `created_at` norādīs migrācijas,
nevis vēsturisko reģistrācijas vai ieraksta izveides laiku. Ja
`animals.photo` esošajā shēmā ir `NOT NULL`, `MODIFY` saglabā esošās foto
vērtības un palielina atļauto datu apjomu. Pēc shēmas atjaunināšanas importē
SQL failu, lai izveidotu trūkstošo `favorites` tabulu. `CREATE TABLE IF NOT EXISTS`
saglabā esošos datus.

## Datubāze un esošie galapunkti

`Database/animal_shelter.sql` izveido lietotāju, dzīvnieku, adopciju,
ziedojumu, favorītu, brīvprātīgo pieteikumu un kontaktziņojumu tabulas.
Dzīvnieku katalogam ir sugas, šķirnes, dzimuma, vecuma, veselības, meklēšanas
un īpašo vajadzību servera filtri. Administrēšana, adopciju statusi, profila
dati, profila attēli un favorīti ir sesijas aizsargāti. Lietotāji redz tikai
savus adopcijas pieteikumus. Lietotāju paroles tiek jauktas ar
`password_hash()` un pārbaudītas ar `password_verify()`. Attēli tiek glabāti
datubāzē kā validēti JPEG, PNG vai WEBP datu URI; attēlu augšupielāde ir
ierobežota līdz 2 MB.
Profila e-pasta adresi var mainīt tieši; formāts tiek pārbaudīts un jau
reģistrētas adreses tiek noraidītas. Donāciju vēsture ir piesaistīta kontam tikai tad, ja lietotājs
iesniedz ziedojuma formu pēc pieteikšanās. Tā kā nav integrēta maksājumu
sistēma, šie ieraksti paliek `not_processed` un netiek uzrādīti kā apmaksāti.

## Pārbaudes ierobežojumi

Repozitorijā nav automatizētu testu konfigurācijas. Integrācijas pārbaudei
vajadzīgs PHP 8+, PDO MySQL draiveris, MySQL/MariaDB un derīga DB
konfigurācija. Šajā izstrādes vidē nav pieejami PHP, Node.js vai MySQL
komandrindas rīki, tāpēc PHP sintaksi, īstu DB savienojumu un hostinga
integrāciju jāpārbauda InfinityFree vidē.

## Projekta komanda

- Jana J.: Front-end pamats un dokumentācija
- Viktorija I.: Datubāze
- Marta Aleksa V.: Front-end
- Vanesa J.: Back-end
