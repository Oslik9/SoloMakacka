<?php

namespace App\Models;

use CodeIgniter\Model;

// Model etap a jejich výsledků.
class StageModel extends Model
{

    protected $table = 'stage';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // Načte etapy ročníku podle pořadí, včetně typu a vítěze.
    public function getStagesForYear($raceYearId)
    {

        return $this->db->table('stage')

            ->select('stage.*, parcour_type.name AS stage_type, rider.first_name AS winner_first_name, rider.last_name AS winner_last_name')
            ->join('parcour_type', 'parcour_type.id = stage.parcour_type', 'left')
            ->join('result', 'result.id_stage = stage.id AND result.type_result = 1 AND result.rank = 1', 'left')
            ->join('rider', 'rider.id = result.id_rider', 'left')
            ->where('stage.id_race_year', $raceYearId)
            ->orderBy('stage.number', 'ASC')
            ->orderBy('stage.id', 'ASC')
            ->get()->getResultArray();
    }

    // Najde etapu daného závodu a doplní údaje o ročníku a typu etapy.
    public function getStageForRace($stageId, $raceId)
    {

        return $this->db->table('stage')
            ->select('stage.*, race_year.real_name, race_year.year, parcour_type.name AS stage_type')
            ->join('race_year', 'race_year.id = stage.id_race_year')
            ->join('parcour_type', 'parcour_type.id = stage.parcour_type', 'left')
            ->where('stage.id', $stageId)
            ->where('race_year.id_race', $raceId)
            ->get()->getRowArray();
    }

    // Načte vybraný typ pořadí s údaji jezdců, seřazený podle umístění.
    public function getStageResults($stageId, $typeResult)
    {

        return $this->db->table('result')
            ->select('result.rank, result.time, rider.first_name, rider.last_name, rider.country')
            ->join('rider', 'rider.id = result.id_rider', 'left')
            ->where('result.id_stage', $stageId)
            ->where('result.type_result', $typeResult)
            ->orderBy('result.rank', 'ASC')
            ->orderBy('result.id', 'ASC')
            ->get()->getResultArray();
    }
}
