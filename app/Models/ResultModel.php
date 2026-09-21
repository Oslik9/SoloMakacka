<?php

namespace App\Models;

use CodeIgniter\Model;

class ResultModel extends Model
{
    protected $table = 'result';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function getStageResults(int $stageId, int $typeResult): array
    {
        return $this->db->table('result res')
            ->select("res.rank, res.time, res.bonification, res.point, res.note, CONCAT_WS(' ', r.first_name, r.last_name) AS rider_name, r.country, res.name_link")
            ->join('rider r', 'r.id = res.id_rider', 'left')
            ->where('res.id_stage', $stageId)
            ->where('res.type_result', $typeResult)
            ->orderBy('res.rank', 'ASC')
            ->get()->getResultArray();
    }
}
