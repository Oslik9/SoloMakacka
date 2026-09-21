<?php

namespace App\Models;

use CodeIgniter\Model;

class StageModel extends Model
{
    protected $table = 'stage';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function getForRaceYear(int $raceYearId): array
    {
        return $this->db->table('stage s')
            ->select("s.id, s.number, s.date, s.distance, s.vertical_meters, s.parcour_type, s.departure, s.arrival, pt.name AS stage_type, CONCAT_WS(' ', r.first_name, r.last_name) AS winner")
            ->join('parcour_type pt', 'pt.id = s.parcour_type', 'left')
            ->join('result res', 'res.id_stage = s.id AND res.type_result = 1 AND res.rank = 1', 'left')
            ->join('rider r', 'r.id = res.id_rider', 'left')
            ->where('s.id_race_year', $raceYearId)
            ->orderBy('s.number', 'ASC')
            ->orderBy('s.date', 'ASC')
            ->get()->getResultArray();
    }
}
