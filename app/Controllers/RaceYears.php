<?php // Controller pro formulář a ukládání ročníků.

namespace App\Controllers; // Prostor jmen controllerů aplikace.

use App\Models\RaceModel; // Model pro výběr a kontrolu závodů.
use App\Models\RaceYearModel; // Model tabulky race_year.

class RaceYears extends BaseController // Zdědí společné helpery a metody CI4.
{
    // Zobrazí formulář; při chybě dostane také chyby a původní hodnoty.
    public function create($errors = [], $values = [])
    {
        $model = new RaceModel(); // Vytvoří model závodů.
        $data['title'] = 'Přidat ročník závodu'; // Nadpis stránky.
        $data['races'] = $model->getMaleCategoryERaces(); // Povolené závody pro dropdown.
        $data['errors'] = $errors; // Chyby předáme přímo do view.
        $data['values'] = $values; // Původní hodnoty formuláře předáme přímo do view.

        foreach ($data['values'] as $field => $value) { // Projde hodnoty vracené do formuláře.
            if (is_array($value)) { // Formulářová pole očekávají jednu hodnotu, ne pole.
                $data['values'][$field] = ''; // Nepovolené pole hodnot nezobrazíme.
            }
        }

        return view('races/create_year', $data); // Vykreslí formulář s připravenými daty.
    }

    // Zkontroluje POST data, uloží ročník a zobrazí potvrzení.
    public function store()
    {
        $values = [ // getPost() načte hodnoty odeslaných polí.
            'real_name' => $this->request->getPost('real_name'), // Název ročníku.
            'race_id' => $this->request->getPost('race_id'), // ID závodu z dropdownu.
            'year' => $this->request->getPost('year'), // Rok ročníku.
            'start_date' => $this->request->getPost('start_date'), // Datum od.
            'end_date' => $this->request->getPost('end_date'), // Datum do.
        ];

        $rules = [ // Běžná validační pravidla CI4.
            'real_name' => 'required|max_length[255]', // Povinný název do 255 znaků.
            'race_id' => 'required|is_natural', // Povinné nezáporné celé číslo.
            'year' => 'required|integer', // Povinný rok jako celé číslo.
            'start_date' => 'required|valid_date[Y-m-d]', // Platné datum začátku.
            'end_date' => 'required|valid_date[Y-m-d]', // Platné datum konce.
        ];

        $logo = $this->request->getFile('logo'); // Načte nahrávaný soubor.
        $hasLogo = $logo && $logo->getName() != ''; // Zjistí, zda byl obrázek vybrán.
        if ($hasLogo) { // Nepovinné logo kontrolujeme jen při nahrání.
            $rules['logo'] = 'uploaded[logo]|max_size[logo,2048]|is_image[logo]|mime_in[logo,image/png,image/jpeg,image/webp,image/gif]|ext_in[logo,png,jpg,jpeg,webp,gif]'; // Obrázek povoleného typu do 2 MB.
        }

        if (!$this->validateData($values, $rules)) { // Ověří formulář pomocí pravidel CI4.
            return $this->create($this->validator->getErrors(), $values); // Znovu zobrazí formulář s chybami a hodnotami.
        }
        if ($values['end_date'] < $values['start_date']) { // Ve formátu Y-m-d lze porovnat pořadí dat.
            return $this->create(['Datum do nesmí být před datem od.'], $values); // Zobrazí chybu přímo u formuláře.
        }

        $raceModel = new RaceModel(); // Připraví model závodů.
        $race = $raceModel->getMaleCategoryERace($values['race_id']); // Ověří mužský závod kategorie E i na serveru.
        if (!$race) { // Neexistující nebo nepovolený závod nelze použít.
            return $this->create(['Vyberte mužský závod kategorie E.'], $values); // Zachová hodnoty a vypíše chybu.
        }

        $logoName = null; // Bez loga se uloží skutečné SQL NULL.
        if ($hasLogo) { // Soubor již prošel validací.
            $logoName = $logo->getRandomName(); // Připraví vlastní název, aby se loga nepřepisovala.
            $logo->move(FCPATH . 'uploads/race-logos', $logoName); // Uloží logo do místní složky.
        }

        $yearModel = new RaceYearModel(); // Vytvoří model ročníků.
        $id = $yearModel->insert([ // Vloží nový záznam a vrátí jeho ID.
            'real_name' => trim($values['real_name']), // Odstraní krajní mezery názvu.
            'id_race' => $values['race_id'], // race_id z formuláře patří do sloupce id_race.
            'year' => $values['year'], // Uloží rok.
            'start_date' => $values['start_date'], // Uloží datum od.
            'end_date' => $values['end_date'], // Uloží datum do.
            'logo' => $logoName, // Název souboru nebo NULL.
            'sex' => 'M', // Mužský ročník.
            'category' => 'E', // Kategorie Elite.
            'country' => $race['country'], // Země vybraného závodu.
            'uci_tour' => 0, // Databáze používá 0 pro neuvedenou UCI tour.
        ]);

        if (!$id) { // Pokud insert() vrátí neúspěch, ročník se neuložil.
            if ($logoName !== null) { // Odstraní pouze případné logo tohoto pokusu.
                unlink(FCPATH . 'uploads/race-logos/' . $logoName); // Soubor bez ročníku nezůstane na disku.
            }
            return $this->create(['Ročník se nepodařilo uložit.'], $values); // Znovu zobrazí formulář.
        }

        session()->setFlashdata('success', 'Ročník „' . trim($values['real_name']) . '“ byl uložen k závodu „' . $race['default_name'] . '“.'); // Zpráva pro následující stránku.
        if ($race['id'] == 124) { // Nový ročník Paříž–Nice se zobrazí v jeho přehledu.
            return redirect()->to(base_url('pariz-nice') . '#rocnik-' . $id); // Otevře přímo kartu nového ročníku.
        }

        return redirect()->to(base_url('race-years/create')); // U ostatních závodů se vrátí na formulář s potvrzením.
    }
}
