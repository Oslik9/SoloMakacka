<?php

namespace App\Controllers;

use App\Models\RaceYearModel;
use App\Models\StageModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ParisNice extends BaseController
{
    public function index()
    {
        $yearModel = new RaceYearModel();
        $stageModel = new StageModel();
        $data['title'] = 'Paříž–Nice';
        $data['raceYears'] = $yearModel->getYearsForRace(124);

        foreach ($data['raceYears'] as $index => $raceYear) {
            $stages = $stageModel->getStagesForYear($raceYear['id']);
            $data['raceYears'][$index]['stages'] = $stages;
            $data['raceYears'][$index]['total_distance'] = null;

            // Celková délka je součet délek uložených etap.
            if (!empty($stages)) {
                $distance = 0;
                foreach ($stages as $stage) {
                    $distance += $stage['distance'];
                }
                $data['raceYears'][$index]['total_distance'] = round($distance);
            }

            $logoName = basename($raceYear['logo'] ?? '');
            $logoPath = 'uploads/race-logos/' . $logoName;
            $data['raceYears'][$index]['logo_url'] = '';
            if (is_file(FCPATH . $logoName)) {
                $logoPath = $logoName;
            }
            if ($logoName != '' && is_file(FCPATH . $logoPath)) {
                $data['raceYears'][$index]['logo_url'] = base_url($logoPath);
            }
        }

        return view('races/paris_nice', $data);
    }

    public function results($stageId, $typeResult)
    {
        // 1 = výsledky etapy, 4 = celkové pořadí po etapě.
        $typeResult = (int) $typeResult;
        if ($typeResult != 1 && $typeResult != 4) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = new StageModel();
        $data['stage'] = $model->getStageForRace($stageId, 124);
        if (!$data['stage']) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['title'] = 'Pořadí v etapě';
        if ($typeResult == 4) {
            $data['title'] = 'Pořadí po etapě';
        }
        $data['typeResult'] = $typeResult;
        $data['results'] = $model->getStageResults($stageId, $typeResult);

        return view('races/results', $data);
    }
}
