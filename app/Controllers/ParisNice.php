<?php

namespace App\Controllers;

use App\Models\RaceYearModel;
use App\Models\StageModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ParisNice extends BaseController
{

    // Zobrazí přehled ročníků Paříž–Nice a jejich etap.
    public function index()
    {
        $yearModel = new RaceYearModel();
        $stageModel = new StageModel();
        $data['title'] = 'Paříž–Nice';
        $data['raceYears'] = $yearModel->getYearsForRace(124);

        // Ke každému ročníku připraví etapy a zaokrouhlenou celkovou délku.
        foreach ($data['raceYears'] as $index => $raceYear) {
            $stages = $stageModel->getStagesForYear($raceYear['id']);
            $data['raceYears'][$index]['stages'] = $stages;

            $data['raceYears'][$index]['total_distance'] = null;

            if (!empty($stages)) {
                $distance = 0;
                foreach ($stages as $stage) {
                    $distance += $stage['distance'];
                }

                $data['raceYears'][$index]['total_distance'] = round($distance);
            }

            // Připraví odkaz na logo, pokud je soubor dostupný.
            $logoName = basename($raceYear['logo'] ?? '');
            $logoPath = 'uploads/race-logos/' . $logoName;
            $data['raceYears'][$index]['logo_url'] = '';

            if ($logoName != '' && is_file(FCPATH . $logoPath)) {
                $data['raceYears'][$index]['logo_url'] = base_url($logoPath);
            }
        }

        return view('races/paris_nice', $data);
    }

    // Zobrazí pořadí v etapě nebo celkové pořadí po etapě.
    public function results($stageId, $typeResult)
    {

        // Ověří povolený typ pořadí a příslušnost etapy k Paříž–Nice.
        $typeResult = (int) $typeResult;

        if ($typeResult != 1 && $typeResult != 4) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = new StageModel();

        $data['stage'] = $model->getStageForRace($stageId, 124);
        if (!$data['stage']) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Připraví nadpis a načte vybrané pořadí.
        $data['title'] = 'Pořadí v etapě';
        if ($typeResult == 4) {
            $data['title'] = 'Pořadí po etapě';
        }
        $data['typeResult'] = $typeResult;
        $data['results'] = $model->getStageResults($stageId, $typeResult);

        // Podle kódů států připraví dostupné místní vlajky.
        foreach ($data['results'] as $index => $result) {
            $country = strtolower(trim($result['country'] ?? ''));
            $data['results'][$index]['flag'] = '';
            if (strlen($country) == 2 && ctype_alpha($country) && is_file(FCPATH . 'node_modules/flag-icons/flags/4x3/' . $country . '.svg')) {
                $data['results'][$index]['flag'] = $country;
            }
        }

        return view('races/results', $data);
    }
}
