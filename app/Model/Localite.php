<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Localite extends Model
{
    public $timestamps = false;

    protected $table = 'localite';

    public $primaryKey = 'id_loc';

    protected $guarded = ['id_loc'];

    public function valid(){
        return true;
    }

    /**
     * Relationships
     *
     */
    public function utilisateur()
    {
        return $this->belongsTo('Kneub\Model\User', 'fk_id_utilisateur', 'id');
    }

    public function pays()
    {
        return $this->hasOne('Kneub\Model\Pays', 'nom', 'fk_pays')->orderBy('nom', 'asc');
    }

    public function recoltes()
    {
        return $this->hasMany('Kneub\Model\Recolte', 'fk_id_loc', 'id_loc');
    }
}
