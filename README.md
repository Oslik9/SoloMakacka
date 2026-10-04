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
Pokud ročník nemá etapy, jeho délka zůstane prázdná. Chybějící vítěz,
typ etapy, čas nebo země se také nechají prázdné. Povinný sloupec
`uci_tour` se u nových ročníků ukládá jako `0`, kterou existující databáze
používá pro neuvedenou UCI tour; konkrétní tour se automaticky nepřiřazuje.

Výpisy obsluhuje controller `ParisNice`, formulář a ukládání controller
`RaceYears`. Model `RaceModel` vybírá závody, `RaceYearModel` načítá a ukládá
ročníky a `StageModel` načítá etapy s výsledky. Tři stránky v `app/Views/races` obsahují
přehled, výsledky a formulář. Sdílejí jen layout `app/Views/templates/main.php`
s navigací, zprávami, patičkou a Bootstrapem z `node_modules`.

Model používá běžné `where`, `join`, `orderBy`, `findAll` a `insert`.
Controller projde ročníky obyčejným `foreach`, načte jejich etapy a sečte
délky v PHP. Formulář čte přes `getPost`, kontroluje pravidly CI4 a ukládá
pomocí `insert`. Soubor SQL slouží jen jako popis původní struktury a dat;
web čte přímo z nahrané databáze `matejwebmaster`.

Roky se čtou ze sloupce `race_year.year`, kde `race_year.id_race = 124`.
Vazby jsou `race.id = race_year.id_race` a `race_year.id = stage.id_race_year`.
Sloupec `stage.id_race_year` se nespojuje přímo s `race.id`.

```sql
SELECT race_year.year AS rok, COUNT(stage.id) AS pocet_etap
FROM race_year
LEFT JOIN stage ON stage.id_race_year = race_year.id
WHERE race_year.id_race = 124
GROUP BY race_year.id, race_year.year
ORDER BY race_year.year DESC;
```

### Ověření

`php vendor/phpunit/phpunit/phpunit --no-coverage`

Testy používají samostatnou SQLite databázi v paměti a ověřují řazení,
zaokrouhlení, vítěze, výsledky 1/4, omezení dropdownu, ukládání bez
časových razítek a vykreslení stránek.
