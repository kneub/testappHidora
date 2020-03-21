<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'id_obs';

    protected $table = 'obs_livre';

    protected $guarded = ['id_obs'];

    public function valid()
    {
        return true;
    }

    public function names()
    {
        return $this->belongsToMany('Kneub\Model\Nom', 'obs_name','id_obs', 'fk_id_name', 'id_obs', 'id_name');
    }

    public function collecteur()
    {
        return $this->belongsTo('Kneub\Model\Collecteur', 'fk_id_collecteur', 'id_coll');
    }

    public function livre()
    {
        return $this->belongsTo('Kneub\Model\Livre', 'fk_livre', 'id_livre_apd');
    }

    public function localite()
    {
        return $this->belongsTo('Kneub\Model\Localite', 'fk_id_loc', 'id_loc');
    }
}
