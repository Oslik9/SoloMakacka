<?php

namespace App\Controllers;

use App\Models\RaceModel;
use App\Models\RaceYearModel;

class RaceYears extends BaseController
{
    public function create()
    {
        $model = new RaceModel();
        $data['title'] = 'Přidat ročník závodu';
        $data['races'] = $model->getMaleCategoryERaces();

        return view('races/create_year', $data);
    }

    public function store()
    {
        $data = [
            'real_name' => $this->request->getPost('real_name'),
            'race_id' => $this->request->getPost('race_id'),
            'year' => $this->request->getPost('year'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
        ];
        $rules = [
            'real_name' => 'required|max_length[255]',
            'race_id' => 'required|is_natural',
            'year' => 'required|integer',
            'start_date' => 'required|valid_date[Y-m-d]',
            'end_date' => 'required|valid_date[Y-m-d]',
        ];

        $logo = $this->request->getFile('logo');
        if ($logo && $logo->getName() != '') {
            $rules['logo'] = 'uploaded[logo]|max_size[logo,2048]|is_image[logo]|mime_in[logo,image/png,image/jpeg,image/webp,image/gif]|ext_in[logo,png,jpg,jpeg,webp,gif]';
        }

        if (!$this->validateData($data, $rules)) {
            return redirect()->to(base_url('race-years/create'))->withInput();
        }
        if ($data['end_date'] < $data['start_date']) {
            return redirect()->to(base_url('race-years/create'))->withInput()
                ->with('error', 'Datum do nesmí být před datem od.');
        }

        $model = new RaceModel();
        $race = $model->getMaleCategoryERace($data['race_id']);
        if (!$race) {
            return redirect()->to(base_url('race-years/create'))->withInput()
                ->with('error', 'Vyberte mužský závod kategorie E.');
        }

        $logoName = null;
        if ($logo && $logo->isValid()) {
            $logoName = $logo->getRandomName();
            $logo->move(FCPATH . 'uploads/race-logos', $logoName);
        }

        $yearModel = new RaceYearModel();
        $id = $yearModel->insert([
            'real_name' => trim($data['real_name']),
            'id_race' => $data['race_id'],
            'year' => $data['year'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'logo' => $logoName,
            'sex' => 'M',
            'category' => 'E',
            'country' => $race['country'],
            // V databázi se 0 používá pro neuvedenou UCI tour.
            'uci_tour' => 0,
        ]);

        if (!$id) {
            if ($logoName !== null) {
                unlink(FCPATH . 'uploads/race-logos/' . $logoName);
            }
            return redirect()->to(base_url('race-years/create'))->withInput()
                ->with('error', 'Ročník se nepodařilo uložit. Zkontrolujte údaje a zkuste to znovu.');
        }

        $redirectUrl = base_url('race-years/create');
        if ($race['id'] == 124) {
            $redirectUrl = base_url('pariz-nice') . '#rocnik-' . $id;
        }

        return redirect()->to($redirectUrl)
            ->with('success', 'Ročník „' . trim($data['real_name']) . '“ byl uložen k závodu „' . $race['default_name'] . '“.');
    }
}
