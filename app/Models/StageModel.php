<?php

// namespace určuje prostor jmen, do kterého tento model patří.
namespace App\Models;

// use umožní psát Model místo celého názvu CodeIgniter\Model.
use CodeIgniter\Model;

// extends Model zdědí databázové metody CI4 a připojení k databázi.
class StageModel extends Model
{
    // Model používá tabulku etap, primární klíč id a záznamy ve formě asociativních polí.
    protected $table = 'stage'; // Tabulka modelu; protected dovoluje přístup v této třídě a jejích potomcích.
    protected $primaryKey = 'id'; // Primární klíč označuje jedinečné ID každé etapy.
    protected $returnType = 'array'; // Metody modelu vracejí jednotlivé záznamy jako asociativní pole.

    // Načte etapy ročníku podle čísla etapy, včetně názvu typu a jména vítěze.
    // $raceYearId je ID ročníku z race_year, nikoli ID závodu z race.
    public function getStagesForYear($raceYearId)
    {
        // LEFT JOIN ponechá ve výpisu i etapy bez vítěze nebo typu.
        // Vítěz je jezdec na prvním místě v pořadí etapy (type_result = 1, rank = 1).
        // AS pojmenuje připojené údaje tak, jak je používá controller a view.
        // $this je tento model, ->db jeho připojení a table() připraví dotaz nad stage.
        // Znak -> přistupuje k vlastnosti nebo metodě objektu; return vrátí výsledek celého řetězce.
        return $this->db->table('stage')
            // select() vybírá sloupce; stage.* znamená všechny sloupce etapy a AS přejmenuje připojené sloupce ve výsledku.
            ->select('stage.*, parcour_type.name AS stage_type, rider.first_name AS winner_first_name, rider.last_name AS winner_last_name')
            ->join('parcour_type', 'parcour_type.id = stage.parcour_type', 'left') // LEFT JOIN připojí typ etapy; bez shody budou jeho údaje null.
            ->join('result', 'result.id_stage = stage.id AND result.type_result = 1 AND result.rank = 1', 'left') // Připojí jen výsledek vítěze této etapy; AND vyžaduje všechny tři podmínky.
            ->join('rider', 'rider.id = result.id_rider', 'left') // Připojí jezdce podle ID z výsledku; bez jezdce etapa stále zůstane ve výpisu.
            ->where('stage.id_race_year', $raceYearId) // where() vybere jen etapy patřící do zadaného ročníku.
            ->orderBy('stage.number', 'ASC') // orderBy() seřadí etapy podle jejich pořadového čísla vzestupně.
            ->orderBy('stage.id', 'ASC') // Pokud mají etapy stejné číslo, seřadí je podle ID vzestupně.
            ->get()->getResultArray(); // get() provede SELECT; getResultArray() vrátí všechny řádky jako pole asociativních polí.
    }

    // Vrátí etapu jen tehdy, když patří k danému závodu; jinak vrátí null.
    // $stageId určuje konkrétní etapu a $raceId závod, do kterého musí patřit.
    public function getStageForRace($stageId, $raceId)
    {
        // Vazba vede přes ročník: stage.id_race_year = race_year.id, race_year.id_race = ID závodu.
        // $this->db je připojení modelu, table() připraví dotaz a return vrátí výsledek celého řetězce.
        return $this->db->table('stage')
            ->select('stage.*, race_year.real_name, race_year.year, parcour_type.name AS stage_type') // select() vybere údaje etapy, jejího ročníku a název typu pod klíčem stage_type.
            ->join('race_year', 'race_year.id = stage.id_race_year') // join() připojí ročník etapy; bez odpovídajícího ročníku se etapa nevrátí.
            ->join('parcour_type', 'parcour_type.id = stage.parcour_type', 'left') // LEFT JOIN doplní typ etapy a ponechá etapu i při chybějícím typu.
            ->where('stage.id', $stageId) // where() omezí dotaz na konkrétní ID etapy.
            ->where('race_year.id_race', $raceId) // Další podmínka ověří, že její ročník patří do požadovaného závodu.
            ->get()->getRowArray(); // get() provede SELECT; getRowArray() vrátí první řádek jako asociativní pole, nebo null bez shody.
    }

    // Načte pořadí v etapě (typ 1) nebo po etapě (typ 4), seřazené podle umístění.
    // $stageId je ID etapy; $typeResult určuje požadovaný typ pořadí.
    public function getStageResults($stageId, $typeResult)
    {
        // LEFT JOIN ponechá výsledek i při chybějícím záznamu jezdce; jeho jméno pak bude null.
        // $this->db použije připojení modelu a table('result') připraví dotaz nad výsledky.
        // return vrátí všechny řádky až po dokončení celého řetězce metod.
        return $this->db->table('result')
            ->select('result.rank, result.time, rider.first_name, rider.last_name, rider.country') // select() vybere umístění, čas, jméno a kód země jezdce pro jeho vlajku.
            ->join('rider', 'rider.id = result.id_rider', 'left') // LEFT JOIN doplní údaje jezdce, ale zachová i výsledek bez odpovídajícího jezdce.
            ->where('result.id_stage', $stageId) // where() ponechá výsledky pouze pro zadanou etapu.
            ->where('result.type_result', $typeResult) // Přidá podmínku požadovaného typu pořadí, aby se různé typy nemíchaly.
            ->orderBy('result.rank', 'ASC') // orderBy() řadí umístění vzestupně: první místo, druhé místo atd.
            ->orderBy('result.id', 'ASC') // Při stejném umístění rozhoduje ID výsledku, také vzestupně.
            ->get()->getResultArray(); // get() provede SELECT; getResultArray() vrátí všechny řádky jako pole asociativních polí.
    }
}
