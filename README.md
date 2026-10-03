## Jsem Student OAUH
Tohle je projekt pro pana Zdendu Hrdinu :D

### Paříž–Nice

Projekt používá CodeIgniter 4 a existující databázi `matejwebmaster` podle
souboru `matejwebmaster.sql`. Připojení je v `app/Config/Database.php`.
Struktura databáze se nemění.

- Přehled: `http://localhost/SoloMakacka/pariz-nice` — závod 124, ročníky od nejnovějšího,
  součet délek etap zaokrouhlený na celé km a etapy podle jejich čísla.
- Výsledky etapy: `/pariz-nice/stage/{id}/results/1`.
- Celkové pořadí po etapě: `/pariz-nice/stage/{id}/results/4`.
- Přidání ročníku: `/race-years/create`. Výběr obsahuje závody,
  které mají v `race_year` mužský ročník (`sex = M`) kategorie E.
  Toto omezení kontroluje i server.

Úvodní adresa `/SoloMakacka/` přesměruje na přehled Paříž–Nice. Navbar
obsahuje přehled a přidání ročníku, aktivní sekce je zvýrazněná. Adresy
neobsahují `index.php`; původní odkazy s `index.php` se přesměrují.

Formulář ukládá název, závod, rok, datum od–do a logo. Rok a data jsou
povinné sloupce tabulky `race_year`. Logo se nahrává do `uploads/race-logos`
(PNG, JPG, WebP nebo GIF, maximálně 2 MB). Původní SQL obsahuje pouze názvy
log, nikoli obrázky; ty lze doplnit do stejné složky pod původními názvy.
Chybějící výsledky ani obrázky se nevymýšlejí.

Databázové dotazy jsou v jediném modelu `RaceModel`. Výpisy i přidávání
obsluhuje controller `ParisNice`. Tři stránky v `app/Views/races` obsahují
přehled, výsledky a formulář. Sdílejí jen layout `app/Views/templates/main.php`
s navigací, zprávami, patičkou a Bootstrapem z `node_modules`.

### Ověření

`php vendor/phpunit/phpunit/phpunit --no-coverage`

Testy používají samostatnou SQLite databázi v paměti a ověřují řazení,
zaokrouhlení, vítěze, výsledky 1/4, omezení dropdownu, ukládání bez
časových razítek a vykreslení stránek.
