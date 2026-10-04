<?php

// namespace zařazuje třídu do prostoru jmen modelů aplikace.
namespace App\Models;

// use zpřístupní základní třídu CodeIgniter\Model pod názvem Model.
use CodeIgniter\Model;

// extends převezme databázové metody třídy Model, například where(), findAll() a insert().
class RaceYearModel extends Model
{
    // Model pracuje s tabulkou ročníků a vrací její záznamy jako asociativní pole.
    protected $table = 'race_year'; // Tabulka pro dotazy modelu; protected znamená přístup v této třídě a jejích potomcích.
    protected $primaryKey = 'id'; // Sloupec id jednoznačně určuje konkrétní ročník.
    protected $returnType = 'array'; // Každý načtený ročník bude asociativní pole, například $rocnik['year'].
    // Pouze tyto sloupce smí Model zapisovat přes insert() nebo update().
    protected $allowedFields = [ // Začátek pole názvů sloupců povolených pro zápis přes model.
        'real_name', 'id_race', 'year', 'start_date', 'end_date', // Název, vazba na závod, rok a datum od–do.
        'uci_tour', 'logo', 'sex', 'category', 'country', // UCI tour, název souboru loga, pohlaví, kategorie a země.
    ];

    // Načte ročníky daného závodu od nejnovějšího; při shodném roce řadí podle data začátku.
    // Parametr $raceId je ID závodu, jehož ročníky požadujeme.
    public function getYearsForRace($raceId)
    {
        // $this je tento model; -> volá jeho metody a return vrátí výsledek celého řetězce.
        return $this->where('id_race', $raceId) // where() omezí dotaz na ročníky, kde id_race odpovídá zadanému závodu.
            ->orderBy('year', 'DESC') // orderBy() seřadí roky sestupně (DESC), tedy od nejnovějšího.
            ->orderBy('start_date', 'DESC') // Při shodném roce rozhodne datum začátku, opět od nejnovějšího.
            ->findAll(); // findAll() provede dotaz nad $table a vrátí všechny odpovídající ročníky podle $returnType.
    }
}
