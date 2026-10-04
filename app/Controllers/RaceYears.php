<?php

namespace App\Controllers;

use App\Models\RaceModel;
use App\Models\RaceYearModel;

class RaceYears extends BaseController
{
    // Zobrazí formulář a načte mužské závody kategorie E do dropdownu.
    public function create()
    {
        $model = new RaceModel();
        $data['title'] = 'Přidat ročník závodu';
        $data['races'] = $model->getMaleCategoryERaces();

        return view('races/create_year', $data);
    }

    // Zkontroluje odeslaný formulář, uloží ročník a případné logo a přesměruje uživatele.
    public function store()
    {
        // getPost() čte hodnoty podle názvů polí odeslaných metodou POST.
        $data = [
            'real_name' => $this->request->getPost('real_name'),
            'race_id' => $this->request->getPost('race_id'),
            'year' => $this->request->getPost('year'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
        ];
        // Pravidla CI4 kontrolují povinné hodnoty, délku názvu a správný formát dat.
        $rules = [
            'real_name' => 'required|max_length[255]',
            'race_id' => 'required|is_natural',
            'year' => 'required|integer',
            'start_date' => 'required|valid_date[Y-m-d]',
            'end_date' => 'required|valid_date[Y-m-d]',
        ];

        // Logo je nepovinné, takže jeho velikost a typ kontrolujeme jen při výběru souboru.
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->getName() != '') {
            $rules['logo'] = 'uploaded[logo]|max_size[logo,2048]|is_image[logo]|mime_in[logo,image/png,image/jpeg,image/webp,image/gif]|ext_in[logo,png,jpg,jpeg,webp,gif]';
        }

        // withInput() zachová vyplněné údaje a chyby pro opětovné zobrazení formuláře.
        if (!$this->validateData($data, $rules)) {
            return redirect()->to(base_url('race-years/create'))->withInput();
        }
        // U ověřeného formátu Y-m-d lze pořadí dat porovnat přímo jako řetězce.
        if ($data['end_date'] < $data['start_date']) {
            return redirect()->to(base_url('race-years/create'))->withInput()
                ->with('error', 'Datum do nesmí být před datem od.');
        }

        $model = new RaceModel();
        // Výběr ověříme i na serveru, protože hodnotu dropdownu lze v požadavku změnit.
        $race = $model->getMaleCategoryERace($data['race_id']);
        if (!$race) {
            return redirect()->to(base_url('race-years/create'))->withInput()
                ->with('error', 'Vyberte mužský závod kategorie E.');
        }

        // Bez obrázku se do databáze uloží SQL NULL.
        $logoName = null;
        if ($logo && $logo->isValid()) {
            // Náhodný název zabrání přepsání jiného loga se stejným původním názvem.
            $logoName = $logo->getRandomName();
            $logo->move(FCPATH . 'uploads/race-logos', $logoName);
        }

        $yearModel = new RaceYearModel();
        // Pole race_id z formuláře patří do sloupce id_race; insert() vrátí ID nového ročníku.
        $id = $yearModel->insert([
            'real_name' => trim($data['real_name']),
            'id_race' => $data['race_id'],
            'year' => $data['year'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'logo' => $logoName,
            // Povinné údaje odpovídají povolené kategorii; zemi převezmeme z vybraného závodu.
            'sex' => 'M',
            'category' => 'E',
            'country' => $race['country'],
            // V databázi se 0 používá pro neuvedenou UCI tour.
            'uci_tour' => 0,
        ]);

        // Pokud insert() vrátí neúspěch, odstraníme případné logo, aby nezůstalo bez ročníku.
        if (!$id) {
            if ($logoName !== null) {
                unlink(FCPATH . 'uploads/race-logos/' . $logoName);
            }
            return redirect()->to(base_url('race-years/create'))->withInput()
                ->with('error', 'Ročník se nepodařilo uložit. Zkontrolujte údaje a zkuste to znovu.');
        }

        // U Paříž–Nice otevřeme nový ročník přes jeho HTML kotvu, jinak se vrátíme na formulář.
        $redirectUrl = base_url('race-years/create');
        if ($race['id'] == 124) {
            $redirectUrl = base_url('pariz-nice') . '#rocnik-' . $id;
        }

        // Zprávu success zobrazí společná šablona po přesměrování.
        return redirect()->to($redirectUrl)
            ->with('success', 'Ročník „' . trim($data['real_name']) . '“ byl uložen k závodu „' . $race['default_name'] . '“.');
    }
}
