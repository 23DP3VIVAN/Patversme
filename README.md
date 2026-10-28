# Dzīvnieku patversmes vietne

## Palaišana lokāli

1. Instalē un atver XAMPP Control Panel.
2. Palaid **Apache** un **MySQL**. Ja Apache nevar startēt, pārbaudi, vai 80. ports nav aizņemts (piemēram, IIS vai cita Apache instance).
3. Ievieto projekta mapi `Patversme` XAMPP `htdocs` mapē, parasti `C:\xampp\htdocs\Patversme`.
4. Atver `http://localhost/phpmyadmin`, izveido datubāzi ar nosaukumu `animal_shelter` un kodējumu `utf8mb4_unicode_ci`.
5. Atver izveidoto datubāzi un importē SQL failus šādā secībā:
   - `Database/animal_shelter.sql`
   - `Database/add_volunteer_user_id.sql`
   - `Database/add_adoption_completed_at.sql`
6. Pārliecinies, ka `config/database.php` lieto lokālo XAMPP lietotāju `root` (pēc noklusējuma bez paroles). Ja lokālajam MySQL esi iestatījis paroli, definē vides mainīgo `DB_PASS` ar šo paroli. Vajadzības gadījumā vari iestatīt arī `DB_HOST`, `DB_NAME`, `DB_USER` un `DB_PORT`.
7. Atver vietni: `http://localhost/Patversme/`.
