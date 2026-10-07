<?php // Začátek PHP kódu.

namespace App\Controllers; // Zařadí controller do prostoru jmen aplikace.

use App\Models\RaceYearModel; // Zpřístupní model ročníků pod jeho krátkým názvem.
use App\Models\StageModel; // Zpřístupní model etap a výsledků pod jeho krátkým názvem.
use CodeIgniter\Exceptions\PageNotFoundException; // Zpřístupní výjimku CI4 pro nenalezenou stránku (404).

class ParisNice extends BaseController // Controller zdědí nastavení helperů a možnosti základního controlleru.
{ // Začátek třídy ParisNice.
    // Načte ročníky Paříž–Nice (id 124), jejich etapy a připraví data pro výpis.
    public function index() // Public metoda může být volána z routy; nemá žádné vstupní parametry.
    { // Začátek metody pro přehled.
        $yearModel = new RaceYearModel(); // new vytvoří objekt modelu pro načítání ročníků.
        $stageModel = new StageModel(); // Vytvoří objekt modelu pro načítání etap.
        $data['title'] = 'Paříž–Nice'; // Do pole pro view uloží nadpis stránky.
        $data['raceYears'] = $yearModel->getYearsForRace(124); // -> zavolá metodu modelu a uloží ročníky závodu 124.

        // Přes index doplňujeme etapy a délku do příslušného ročníku v poli pro view.
        foreach ($data['raceYears'] as $index => $raceYear) { // Projde ročníky; $index je jejich klíč v poli, $raceYear aktuální záznam.
            $stages = $stageModel->getStagesForYear($raceYear['id']); // Načte etapy aktuálního ročníku podle jeho ID.
            $data['raceYears'][$index]['stages'] = $stages; // Přidá načtené etapy do odpovídajícího ročníku pro view.
            // Bez etap necháme délku prázdnou, místo abychom zobrazili smyšlenou nulu.
            $data['raceYears'][$index]['total_distance'] = null; // null zatím označuje neuvedenou celkovou délku.

            // Celková délka je součet délek uložených etap.
            if (!empty($stages)) { // empty() ověří prázdné pole; ! obrátí výsledek, takže pokračujeme jen s etapami.
                $distance = 0; // Připraví nulový součet vzdáleností pro tento ročník.
                foreach ($stages as $stage) { // Postupně vezme každou etapu z načteného pole.
                    $distance += $stage['distance']; // += přičte délku etapy k dosavadnímu součtu.
                } // Konec sčítání etap.
                // Součet zaokrouhlíme na celé kilometry až po sečtení všech etap.
                $data['raceYears'][$index]['total_distance'] = round($distance); // round() zaokrouhlí součet na celé km a uloží jej pro view.
            } // Konec podmínky pro ročník s etapami.

            // basename() vezme jen název souboru; chybějící logo se převede na prázdný řetězec.
            $logoName = basename($raceYear['logo'] ?? ''); // ?? použije prázdný text při null; basename() ponechá jen název souboru.
            $logoPath = 'uploads/race-logos/' . $logoName; // Tečka spojí cestu složky a název loga do jednoho řetězce.
            $data['raceYears'][$index]['logo_url'] = ''; // Výchozí prázdná URL znamená, že se obrázek nezobrazí.
            // Odkaz na obrázek vytvoříme jen tehdy, když soubor opravdu existuje.
            if ($logoName != '' && is_file(FCPATH . $logoPath)) { // && vyžaduje obě podmínky: neprázdný název i existující soubor.
                $data['raceYears'][$index]['logo_url'] = base_url($logoPath); // Vytvoří úplnou webovou adresu obrázku pro view.
            } // Konec přípravy URL loga.
        } // Konec zpracování všech ročníků.

        // Klíče pole $data budou ve view dostupné jako proměnné.
        return view('races/paris_nice', $data); // view() vykreslí stránku s daty; return vrátí její HTML a ukončí metodu.
    } // Konec metody index().

    // Zobrazí vybraný typ pořadí pro etapu; obě hodnoty přicházejí z URL.
    public function results($stageId, $typeResult) // Public metoda přijímá ID etapy a typ pořadí předané routou.
    { // Začátek metody pro výsledky.
        // 1 = výsledky etapy, 4 = celkové pořadí po etapě.
        $typeResult = (int) $typeResult; // (int) převede hodnotu z URL na celé číslo.
        // Nepovolený typ pořadí vrátí chybu 404 (stránka nebyla nalezena).
        if ($typeResult != 1 && $typeResult != 4) { // Pokračuje do chyby, pouze když typ není ani 1, ani 4.
            throw PageNotFoundException::forPageNotFound(); // throw vyvolá výjimku; CI4 ukončí požadavek odpovědí 404.
        } // Konec kontroly typu pořadí.

        $model = new StageModel(); // Vytvoří model pro načtení etapy a jejích výsledků.
        // Nestačí existence etapy: musí patřit do některého ročníku Paříž–Nice.
        $data['stage'] = $model->getStageForRace($stageId, 124); // Vyhledá zadanou etapu pouze mezi ročníky závodu 124.
        if (!$data['stage']) { // ! ověří, že model žádnou odpovídající etapu nevrátil.
            throw PageNotFoundException::forPageNotFound(); // Nenalezená nebo cizí etapa způsobí odpověď 404.
        } // Konec kontroly etapy.

        // Podle typu změníme nadpis a načteme odpovídající výsledky.
        $data['title'] = 'Pořadí v etapě'; // Nastaví výchozí nadpis pro výsledky typu 1.
        if ($typeResult == 4) { // == porovná typ s hodnotou 4, tedy s celkovým pořadím po etapě.
            $data['title'] = 'Pořadí po etapě'; // Pro typ 4 změní nadpis stránky.
        } // Konec výběru nadpisu.
        $data['typeResult'] = $typeResult; // Předá typ do view pro zvýraznění aktuálního tlačítka.
        $data['results'] = $model->getStageResults($stageId, $typeResult); // Načte výsledky vybrané etapy a typu, seřazené podle umístění.

        foreach ($data['results'] as $index => $result) { // Ke každému výsledku připraví kód místní vlajky.
            $country = strtolower(trim($result['country'] ?? '')); // Převede zkratku země na malá písmena.
            $data['results'][$index]['flag'] = ''; // Bez známé vlajky necháme buňku prázdnou.
            if (strlen($country) == 2 && ctype_alpha($country) && is_file(FCPATH . 'node_modules/flag-icons/flags/4x3/' . $country . '.svg')) { // Ověří dvoupísmenný kód a existenci jeho SVG.
                $data['results'][$index]['flag'] = $country; // Například cz odpovídá třídě fi-cz.
            }
        }

        return view('races/results', $data); // Vykreslí stránku výsledků s připravenými daty a vrátí její HTML.
    } // Konec metody results().
} // Konec controlleru ParisNice.
