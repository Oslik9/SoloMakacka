<?php

namespace App\Controllers;

use App\Models\RaceYearModel;
use App\Models\StageModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ParisNice extends BaseController
{
    // Načte ročníky Paříž–Nice (id 124), jejich etapy a připraví data pro výpis.
    public function index()
    {
        $yearModel = new RaceYearModel();
        $stageModel = new StageModel();
        $data['title'] = 'Paříž–Nice';
        $data['raceYears'] = $yearModel->getYearsForRace(124);

        // Přes index doplňujeme etapy a délku do příslušného ročníku v poli pro view.
        foreach ($data['raceYears'] as $index => $raceYear) {
            $stages = $stageModel->getStagesForYear($raceYear['id']);
            $data['raceYears'][$index]['stages'] = $stages;
            // Bez etap necháme délku prázdnou, místo abychom zobrazili smyšlenou nulu.
            $data['raceYears'][$index]['total_distance'] = null;

            // Celková délka je součet délek uložených etap.
            if (!empty($stages)) {
                $distance = 0;
                foreach ($stages as $stage) {
                    $distance += $stage['distance'];
                }
                // Součet zaokrouhlíme na celé kilometry až po sečtení všech etap.
                $data['raceYears'][$index]['total_distance'] = round($distance);
            }

            // basename() vezme jen název souboru; chybějící logo se převede na prázdný řetězec.
            $logoName = basename($raceYear['logo'] ?? '');
            $logoPath = 'uploads/race-logos/' . $logoName;
            $data['raceYears'][$index]['logo_url'] = '';
            // FCPATH je kořen webu. Původní logo může být i zde, nejen v uploads/race-logos.
            if (is_file(FCPATH . $logoName)) {
                $logoPath = $logoName;
            }
            // Odkaz na obrázek vytvoříme jen tehdy, když soubor opravdu existuje.
            if ($logoName != '' && is_file(FCPATH . $logoPath)) {
                $data['raceYears'][$index]['logo_url'] = base_url($logoPath);
            }
        }

        // Klíče pole $data budou ve view dostupné jako proměnné.
        return view('races/paris_nice', $data);
    }

    // Zobrazí vybraný typ pořadí pro etapu; obě hodnoty přicházejí z URL.
    public function results($stageId, $typeResult)
    {
        // 1 = výsledky etapy, 4 = celkové pořadí po etapě.
        $typeResult = (int) $typeResult;
        // Nepovolený typ pořadí vrátí chybu 404 (stránka nebyla nalezena).
        if ($typeResult != 1 && $typeResult != 4) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = new StageModel();
        // Nestačí existence etapy: musí patřit do některého ročníku Paříž–Nice.
        $data['stage'] = $model->getStageForRace($stageId, 124);
        if (!$data['stage']) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Podle typu změníme nadpis a načteme odpovídající výsledky.
        $data['title'] = 'Pořadí v etapě';
        if ($typeResult == 4) {
            $data['title'] = 'Pořadí po etapě';
        }
        $data['typeResult'] = $typeResult;
        $data['results'] = $model->getStageResults($stageId, $typeResult);

        return view('races/results', $data);
    }
}
