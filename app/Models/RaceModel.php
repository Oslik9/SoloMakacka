<?php

namespace App\Models;

use CodeIgniter\Model;

class RaceModel extends Model
{
    protected $table = 'race';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function getMaleCategoryERaces()
    {
        // Pohlaví a kategorie jsou v race_year, proto připojujeme ročníky.
        return $this->db->table('race')
            ->select('race.id, race.default_name')
            ->distinct()
            ->join('race_year', 'race_year.id_race = race.id')
            ->where('race_year.sex', 'M')
            ->where('race_year.category', 'E')
            ->orderBy('race.default_name', 'ASC')
            ->get()->getResultArray();
    }

    public function getMaleCategoryERace($raceId)
    {
        return $this->db->table('race')
            ->select('race.id, race.country')
            ->join('race_year', 'race_year.id_race = race.id')
            ->where('race.id', $raceId)
            ->where('race_year.sex', 'M')
            ->where('race_year.category', 'E')
            ->get()->getRowArray();
    }
}
