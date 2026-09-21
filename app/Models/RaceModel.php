<?php

namespace App\Models;

use CodeIgniter\Model;

class RaceModel extends Model
{
    protected $table = 'race';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['default_name', 'link', 'country', 'type'];

    // Závody, které mají alespoň jeden mužský ročník kategorie E.
    public function getMaleCategoryERaces(): array
    {
        return $this->select('race.id, race.default_name')
            ->join('race_year', 'race_year.id_race = race.id')
            ->where('race_year.sex', 'M')
            ->where('race_year.category', 'E')
            ->groupBy('race.id, race.default_name')
            ->orderBy('race.default_name', 'ASC')
            ->findAll();
    }
}
