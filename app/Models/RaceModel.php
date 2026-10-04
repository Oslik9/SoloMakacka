<?php

// namespace určuje, že tato třída patří mezi modely aplikace.
namespace App\Models;

// use umožní používat základní třídu CI4 pod krátkým názvem Model.
use CodeIgniter\Model;

// extends Model zdědí běžné databázové metody CI4, například findAll() a insert().
class RaceModel extends Model
{
    // Název tabulky, její primární klíč a načítání záznamů jako asociativních polí.
    protected $table = 'race'; // $table určuje tabulku modelu; protected zpřístupní vlastnost třídě a jejím potomkům.
    protected $primaryKey = 'id'; // $primaryKey určuje sloupec, který jednoznačně identifikuje záznam.
    protected $returnType = 'array'; // Metody modelu, např. find(), vracejí záznam jako pole se jmény sloupců coby klíči.

    // Vrátí závody s alespoň jedním mužským ročníkem kategorie E, seřazené podle názvu.
    public function getMaleCategoryERaces()
    {
        // Pohlaví a kategorie jsou v race_year, proto připojujeme ročníky.
        // distinct() zabrání opakování stejného závodu, pokud má více odpovídajících ročníků.
        // $this je tento model; ->db je jeho připojení k databázi a -> volá metodu nebo čte vlastnost objektu.
        // return vrátí výsledek celého řetězce, tedy až po provedení getResultArray().
        return $this->db->table('race')
            // table('race') připravilo sestavení SQL dotazu nad tabulkou race; dotaz ještě neproběhl.
            ->select('race.id, race.default_name') // select() určí, které sloupce má dotaz vrátit.
            ->distinct() // distinct() odstraní duplicitní kombinace vybraného ID a názvu závodu.
            ->join('race_year', 'race_year.id_race = race.id') // join() připojí ročníky přes ID závodu; bez shody se závod nevrátí.
            ->where('race_year.sex', 'M') // where() přidá podmínku rovnosti: ponechá pouze mužské ročníky.
            ->where('race_year.category', 'E') // Další where() se přidá přes AND: stejný ročník musí mít i kategorii E.
            ->orderBy('race.default_name', 'ASC') // orderBy() nastaví řazení podle názvu; ASC znamená vzestupně.
            ->get()->getResultArray(); // get() provede SELECT; getResultArray() vrátí všechny řádky jako pole asociativních polí.
    }

    // Vrátí jeden povolený závod podle ID, včetně země; bez shody vrátí null.
    // Parametr $raceId obsahuje ID závodu předané při zavolání metody.
    public function getMaleCategoryERace($raceId)
    {
        // $this->db použije připojení tohoto modelu; table() připraví dotaz a return vrátí výsledek celého řetězce.
        return $this->db->table('race')
            ->select('race.id, race.default_name, race.country') // select() vybere ID, název a zemi závodu.
            ->join('race_year', 'race_year.id_race = race.id') // join() spojí závod s jeho ročníky pomocí uvedené podmínky.
            ->where('race.id', $raceId) // where() ponechá pouze závod, jehož ID odpovídá parametru $raceId.
            ->where('race_year.sex', 'M') // Přidá podmínku, že připojený ročník je mužský.
            ->where('race_year.category', 'E') // Přidá podmínku, že tentýž ročník patří do kategorie E.
            ->get()->getRowArray(); // get() provede SELECT; getRowArray() vrátí první řádek jako asociativní pole, nebo null bez shody.
    }
}
