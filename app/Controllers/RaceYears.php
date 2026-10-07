<?php

namespace App\Controllers;

use App\Models\RaceModel;
use App\Models\RaceYearModel;

class RaceYears extends BaseController
{

    // Připraví formulář, povolené závody a případné chyby s původními hodnotami.
    public function create($errors = [], $values = [])
    {
        $model = new RaceModel();
        $data['title'] = 'Přidat ročník závodu';
        $data['races'] = $model->getMaleCategoryERaces();
        $data['errors'] = $errors;
        $data['values'] = $values;

        // Ošetří nepovolené hodnoty vracené do formuláře.
        foreach ($data['values'] as $field => $value) {
            if (is_array($value)) {
                $data['values'][$field] = '';
            }
        }

        return view('races/create_year', $data);
    }

    // Zpracuje odeslaný formulář a uloží nový ročník.
    public function store()
    {
        // Načte údaje odeslané z formuláře.
        $values = [
            'real_name' => $this->request->getPost('real_name'),
            'race_id' => $this->request->getPost('race_id'),
            'year' => $this->request->getPost('year'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
        ];

        // Připraví kontrolu povinných údajů a případného loga.
        $rules = [
            'real_name' => 'required|max_length[255]',
            'race_id' => 'required|is_natural',
            'year' => 'required|integer',
            'start_date' => 'required|valid_date[Y-m-d]',
            'end_date' => 'required|valid_date[Y-m-d]',
        ];

        $logo = $this->request->getFile('logo');
        $hasLogo = $logo && $logo->getName() != '';
        if ($hasLogo) {
            $rules['logo'] = 'uploaded[logo]|max_size[logo,2048]|is_image[logo]|mime_in[logo,image/png,image/jpeg,image/webp,image/gif]|ext_in[logo,png,jpg,jpeg,webp,gif]';
        }

        // Při chybě znovu zobrazí formulář s vyplněnými hodnotami.
        if (!$this->validateData($values, $rules)) {
            return $this->create($this->validator->getErrors(), $values);
        }
        if ($values['end_date'] < $values['start_date']) {
            return $this->create(['Datum do nesmí být před datem od.'], $values);
        }

        // Ověří, že vybraný závod má mužský ročník kategorie E.
        $raceModel = new RaceModel();
        $race = $raceModel->getMaleCategoryERace($values['race_id']);
        if (!$race) {
            return $this->create(['Vyberte mužský závod kategorie E.'], $values);
        }

        // Uloží nepovinné logo; bez obrázku zůstane hodnota NULL.
        $logoName = null;
        if ($hasLogo) {
            $logoName = $logo->getRandomName();
            $logo->move(FCPATH . 'uploads/race-logos', $logoName);
        }

        // Uloží ročník do tabulky race_year.
        $yearModel = new RaceYearModel();
        $id = $yearModel->insert([
            'real_name' => trim($values['real_name']),
            'id_race' => $values['race_id'],
            'year' => $values['year'],
            'start_date' => $values['start_date'],
            'end_date' => $values['end_date'],
            'logo' => $logoName,
            'sex' => 'M',
            'category' => 'E',
            'country' => $race['country'],
            'uci_tour' => 0,
        ]);

        // Při neúspěšném zápisu odstraní případné logo a zobrazí chybu.
        if (!$id) {
            if ($logoName !== null) {
                unlink(FCPATH . 'uploads/race-logos/' . $logoName);
            }
            return $this->create(['Ročník se nepodařilo uložit.'], $values);
        }

        // Potvrdí uložení a přesměruje na nový ročník nebo zpět na formulář.
        session()->setFlashdata('success', 'Ročník „' . trim($values['real_name']) . '“ byl uložen k závodu „' . $race['default_name'] . '“.');
        if ($race['id'] == 124) {
            return redirect()->to(base_url('pariz-nice') . '#rocnik-' . $id);
        }

        return redirect()->to(base_url('race-years/create'));
    }
}
