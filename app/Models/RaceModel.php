<?php

namespace App\Models;

use CodeIgniter\Model;

class RaceModel extends Model
{
    protected $table = 'race_year';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'real_name', 'id_race', 'year', 'start_date', 'end_date',
        'uci_tour', 'logo', 'sex', 'category', 'country',
    ];

    public function getYearsForRace(int $raceId): array
    {
        return $this->db->table('race_year ry')
            ->select('ry.*, ROUND(COALESCE(SUM(s.distance), 0)) AS total_distance')
            ->join('stage s', 's.id_race_year = ry.id', 'left')
            ->where('ry.id_race', $raceId)
            ->groupBy('ry.id')
            ->orderBy('ry.year', 'DESC')
            ->orderBy('ry.start_date', 'DESC')
            ->orderBy('ry.id', 'DESC')
            ->get()->getResultArray();
    }

    public function getStagesForYear(int $raceYearId): array
    {
        return $this->db->table('stage s')
            ->select('s.id, s.number, s.date, s.note, s.distance, s.vertical_meters, s.departure, s.arrival, pt.name AS stage_type, r.first_name AS winner_first_name, r.last_name AS winner_last_name')
            ->join('parcour_type pt', 'pt.id = s.parcour_type', 'left')
            ->join('result res', 'res.id_stage = s.id AND res.type_result = 1 AND res.rank = 1', 'left')
            ->join('rider r', 'r.id = res.id_rider', 'left')
            ->where('s.id_race_year', $raceYearId)
            ->orderBy('s.number', 'ASC')
            ->orderBy('s.date', 'ASC')
            ->orderBy('s.id', 'ASC')
            ->get()->getResultArray();
    }

    public function getStageForRace(int $stageId, int $raceId): ?array
    {
        return $this->db->table('stage s')
            ->select('s.*, ry.real_name, ry.year')
            ->join('race_year ry', 'ry.id = s.id_race_year')
            ->where('s.id', $stageId)
            ->where('ry.id_race', $raceId)
            ->get()->getRowArray();
    }

    public function getStageResults(int $stageId, int $typeResult): array
    {
        return $this->db->table('result res')
            ->select('res.rank, res.time, res.note, r.first_name, r.last_name, r.country')
            ->join('rider r', 'r.id = res.id_rider', 'left')
            ->where('res.id_stage', $stageId)
            ->where('res.type_result', $typeResult)
            ->orderBy('res.rank', 'ASC')
            ->orderBy('res.id', 'ASC')
            ->get()->getResultArray();
    }

    public function getMaleCategoryERaces(): array
    {
        return $this->db->table('race')
            ->select('race.id, race.default_name, race.country')
            ->distinct()
            ->join('race_year', 'race_year.id_race = race.id')
            ->where('race_year.sex', 'M')
            ->where('race_year.category', 'E')
            ->orderBy('race.default_name', 'ASC')
            ->get()->getResultArray();
    }

    public function getMaleCategoryERace(int $raceId): ?array
    {
        return $this->db->table('race')
            ->select('race.id, race.default_name, race.country')
            ->join('race_year', 'race_year.id_race = race.id')
            ->where('race.id', $raceId)
            ->where('race_year.sex', 'M')
            ->where('race_year.category', 'E')
            ->get()->getRowArray();
    }
}
