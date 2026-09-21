<?php

namespace App\Controllers;

use App\Models\RaceYearModel;
use App\Models\RaceModel;
use App\Models\StageModel;
use App\Models\ResultModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ParisNice extends BaseController
{
    private const RACE_ID = 124;

    public function index()
    {
        $db = db_connect();
        $raceYears = $db->table('race_year ry')
            ->select('ry.*, ROUND(COALESCE(SUM(s.distance), 0)) AS total_distance')
            ->join('stage s', 's.id_race_year = ry.id', 'left')
            ->where('ry.id_race', self::RACE_ID)
            ->groupBy('ry.id')
            ->orderBy('ry.year', 'DESC')
            ->orderBy('ry.start_date', 'DESC')
            ->get()->getResultArray();

        $stageModel = new StageModel();
        foreach ($raceYears as &$raceYear) {
            $raceYear['stages'] = $stageModel->getForRaceYear((int) $raceYear['id']);
        }
        unset($raceYear);

        return view('races/paris_nice', [
            'title' => 'Paříž–Nice',
            'raceYears' => $raceYears,
        ]);
    }

    public function create()
    {
        $raceModel = new RaceModel();
        return view('races/create_year', [
            'title' => 'Přidat ročník závodu',
            'races' => $raceModel->getMaleCategoryERaces(),
            'validation' => service('validation'),
        ]);
    }

    public function store()
    {
        $rules = [
            'real_name' => 'required|max_length[255]',
            'race_id'   => 'required|is_natural_no_zero',
            'year'      => 'required|integer|greater_than_equal_to[1800]|less_than_equal_to[2200]',
            'start_date'=> 'required|valid_date[Y-m-d]',
            'end_date'  => 'required|valid_date[Y-m-d]',
            'logo'      => 'uploaded[logo]|max_size[logo,2048]|is_image[logo]|mime_in[logo,image/png,image/jpeg,image/webp,image/gif]'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        if ($this->request->getPost('end_date') < $this->request->getPost('start_date')) {
            return redirect()->back()->withInput()->with('error', 'Datum konce nesmí být před datem začátku.');
        }

        $raceId = (int) $this->request->getPost('race_id');
        $allowed = array_column((new RaceModel())->getMaleCategoryERaces(), 'id');
        if (! in_array($raceId, array_map('intval', $allowed), true)) {
            return redirect()->back()->withInput()->with('error', 'Vybraný závod není mužský závod kategorie E.');
        }

        $logo = $this->request->getFile('logo');
        $newName = $logo->getRandomName();
        $logo->move(FCPATH . 'uploads/race-logos', $newName);

        (new RaceYearModel())->insert([
            'real_name'  => trim((string) $this->request->getPost('real_name')),
            'id_race'    => $raceId,
            'year'       => (int) $this->request->getPost('year'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date'   => $this->request->getPost('end_date'),
            'logo'       => 'uploads/race-logos/' . $newName,
            // Zadání omezuje výběr na tuto skupinu; uložíme ji i do ročníku.
            'sex'        => 'M',
            'category'   => 'E',
            'uci_tour'   => 0,
            'country'    => '',
        ]);

        return redirect()->to(site_url('pariz-nice'))->with('success', 'Ročník byl přidán.');
    }

    public function results(int $stageId, int $typeResult)
    {
        if (! in_array($typeResult, [1, 4], true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();
        $stage = $db->table('stage s')
            ->select('s.*, ry.real_name, ry.year')
            ->join('race_year ry', 'ry.id = s.id_race_year')
            ->where('s.id', $stageId)
            ->where('ry.id_race', self::RACE_ID)
            ->get()->getRowArray();

        if (! $stage) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('races/results', [
            'title' => $typeResult === 1 ? 'Pořadí v etapě' : 'Pořadí po etapě',
            'stage' => $stage,
            'typeResult' => $typeResult,
            'results' => (new ResultModel())->getStageResults($stageId, $typeResult),
        ]);
    }
}
