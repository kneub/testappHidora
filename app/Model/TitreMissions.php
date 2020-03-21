<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class TitreMissions extends Model
{
    public $timestamps = false;

    public $primaryKey = 'no_titre_missions';

    protected $table = 'titre_missions';

    protected $guarded = ['no_titre_missions'];

    public function valid()
    {
        return true;
    }

    public function recoltes()
    {
        return $this->hasMany('Kneub\Model\Recolte', 'fk_titre_mission', 'no_titre_missions');
    }

}
