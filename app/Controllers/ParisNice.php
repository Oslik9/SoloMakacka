<?php

namespace App\Controllers;

use App\Models\RaceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

class ParisNice extends BaseController
{
    private const RACE_ID = 124;

    public function index(): string
    {
        $model = new RaceModel();
        $raceYears = $model->getYearsForRace(self::RACE_ID);

        foreach ($raceYears as &$raceYear) {
            $raceYear['stages'] = $model->getStagesForYear((int) $raceYear['id']);
            $raceYear['logo_url'] = $this->getLogoUrl($raceYear['logo']);
        }
        unset($raceYear);

        return view('races/paris_nice', [
            'title' => 'Paříž–Nice',
            'raceYears' => $raceYears,
        ]);
    }

    public function results(int $stageId, int $typeResult): string
    {
        if (! in_array($typeResult, [1, 4], true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = new RaceModel();
        $stage = $model->getStageForRace($stageId, self::RACE_ID);
        if (! $stage) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('races/results', [
            'title' => $typeResult === 1 ? 'Pořadí v etapě' : 'Pořadí po etapě',
            'stage' => $stage,
            'typeResult' => $typeResult,
            'results' => $model->getStageResults($stageId, $typeResult),
        ]);
    }

    public function create(): string
    {
        return view('races/create_year', [
            'title' => 'Přidat ročník závodu',
            'races' => (new RaceModel())->getMaleCategoryERaces(),
        ]);
    }

    public function store(): RedirectResponse
    {
        $data = $this->request->getPost(['real_name', 'race_id', 'year', 'start_date', 'end_date']);
        if (is_string($data['real_name'])) {
            $data['real_name'] = trim($data['real_name']);
        }

        $rules = [
            'real_name' => ['label' => 'Název ročníku', 'rules' => 'required|max_length[255]'],
            'race_id' => ['label' => 'Závod', 'rules' => 'required|is_natural'],
            'year' => ['label' => 'Rok', 'rules' => 'required|integer|greater_than_equal_to[1800]|less_than_equal_to[2200]'],
            'start_date' => ['label' => 'Datum od', 'rules' => 'required|valid_date[Y-m-d]'],
            'end_date' => ['label' => 'Datum do', 'rules' => 'required|valid_date[Y-m-d]'],
            'logo' => [
                'label' => 'Logo závodu',
                'rules' => 'uploaded[logo]|max_size[logo,2048]|is_image[logo]|mime_in[logo,image/png,image/jpeg,image/webp,image/gif]|ext_in[logo,png,jpg,jpeg,webp,gif]',
                'errors' => [
                    'uploaded' => 'Vyberte logo závodu.',
                    'max_size' => 'Logo může mít nejvýše 2 MB.',
                    'is_image' => 'Logo musí být obrázek.',
                    'mime_in' => 'Použijte obrázek PNG, JPG, WebP nebo GIF.',
                    'ext_in' => 'Použijte obrázek PNG, JPG, WebP nebo GIF.',
                ],
            ],
        ];

        if (! $this->validateData($data, $rules)) {
            return redirect()->to(site_url('race-years/create'))->withInput();
        }

        if ($data['end_date'] < $data['start_date']) {
            return redirect()->to(site_url('race-years/create'))->withInput()
                ->with('error', 'Datum do nesmí být před datem od.');
        }

        if ((int) substr($data['start_date'], 0, 4) !== (int) $data['year']) {
            return redirect()->to(site_url('race-years/create'))->withInput()
                ->with('error', 'Rok ročníku musí odpovídat roku zahájení závodu.');
        }

        $model = new RaceModel();
        $race = $model->getMaleCategoryERace((int) $data['race_id']);
        if (! $race) {
            return redirect()->to(site_url('race-years/create'))->withInput()
                ->with('error', 'Vyberte mužský závod kategorie E.');
        }

        $logo = $this->request->getFile('logo');
        $logoPath = 'uploads/race-logos/' . $logo->getRandomName();

        try {
            $logo->move(FCPATH . 'uploads/race-logos', basename($logoPath));
            $id = $model->insert([
                'real_name' => $data['real_name'],
                'id_race' => (int) $race['id'],
                'year' => (int) $data['year'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'logo' => $logoPath,
                'sex' => 'M',
                'category' => 'E',
                'country' => $race['country'],
                'uci_tour' => 0,
            ]);

            if ($id === false) {
                throw new \RuntimeException('Ročník nebyl uložen.');
            }
        } catch (Throwable $exception) {
            if ($logo->hasMoved() && is_file(FCPATH . $logoPath)) {
                unlink(FCPATH . $logoPath);
            }
            log_message('error', 'Přidání ročníku selhalo: {message}', ['message' => $exception->getMessage()]);

            return redirect()->to(site_url('race-years/create'))->withInput()
                ->with('error', 'Ročník se nepodařilo uložit. Zkuste to znovu a vyberte logo.');
        }

        $target = 'pariz-nice' . ((int) $race['id'] === self::RACE_ID ? '#rocnik-' . $id : '');
        return redirect()->to(site_url($target))
            ->with('success', 'Ročník „' . $data['real_name'] . '“ (' . $data['year'] . ') byl přidán.');
    }

    private function getLogoUrl(string $logo): ?string
    {
        if ($logo === '' || str_contains($logo, '..') || str_contains($logo, '\\')) {
            return null;
        }

        foreach ([$logo, 'uploads/race-logos/' . basename($logo)] as $path) {
            if (is_file(FCPATH . $path)) {
                return base_url($path);
            }
        }

        return null;
    }
}
