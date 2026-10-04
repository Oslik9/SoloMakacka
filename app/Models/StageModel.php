<?php

namespace App\Models;

use CodeIgniter\Model;

class StageModel extends Model
{
    protected $table = 'stage';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function getStagesForYear($raceYearId)
    {
        // LEFT JOIN ponechá ve výpisu i etapy bez vítěze nebo typu.
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

    public function getStageForRace($stageId, $raceId)
    {
        return $this->db->table('stage')
            ->select('stage.*, race_year.real_name, race_year.year')
            ->join('race_year', 'race_year.id = stage.id_race_year')
            ->where('stage.id', $stageId)
            ->where('race_year.id_race', $raceId)
            ->get()->getRowArray();
    }

    public function getStageResults($stageId, $typeResult)
    {
        return $this->db->table('result')
            ->select('result.rank, result.time, result.note, rider.first_name, rider.last_name, rider.country')
            ->join('rider', 'rider.id = result.id_rider', 'left')
            ->where('result.id_stage', $stageId)
            ->where('result.type_result', $typeResult)
            ->orderBy('result.rank', 'ASC')
            ->orderBy('result.id', 'ASC')
            ->get()->getResultArray();
    }
}
