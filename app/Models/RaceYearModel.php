<?php

namespace App\Models;

use CodeIgniter\Model;

class RaceYearModel extends Model
{
    protected $table = 'race_year';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'real_name', 'id_race', 'year', 'start_date', 'end_date',
        'uci_tour', 'logo', 'sex', 'category', 'country',
    ];

    public function getYearsForRace($raceId)
    {
        return $this->where('id_race', $raceId)
            ->orderBy('year', 'DESC')
            ->orderBy('start_date', 'DESC')
            ->findAll();
    }
}
