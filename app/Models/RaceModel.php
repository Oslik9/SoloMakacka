<?php

namespace App\Models;

use CodeIgniter\Model;

// Model závodů pro výběr ve formuláři.
class RaceModel extends Model
{

    protected $table = 'race';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // Načte mužské závody kategorie E bez duplicit, seřazené podle názvu.
    public function getMaleCategoryERaces()
    {

        return $this->db->table('race')

            ->select('race.id, race.default_name')
            ->distinct()
            ->join('race_year', 'race_year.id_race = race.id')
            ->where('race_year.sex', 'M')
            ->where('race_year.category', 'E')
            ->orderBy('race.default_name', 'ASC')
            ->get()->getResultArray();
    }

    // Najde povolený závod podle ID, včetně jeho země.
    public function getMaleCategoryERace($raceId)
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
