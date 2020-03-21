<?php

namespace Kneub\Model;

use \Illuminate\Database\Eloquent\Model;

class Nom extends Model
{
    public $timestamps = false;

    protected $table = 'nom';

    protected $guarded = ['id_name'];


    /**
     * Relationships
     *
     */

    public function taxon()
    {
        return $this->hasOne('Kneub\Model\Taxon', 'id_name', 'id_name');
    }

    public function famille()
    {
        return $this->belongsTo('Kneub\Model\Famille', 'fk_famille', 'famille');
    }

    public function recoltes()
    {
        return $this->hasMany('Kneub\Model\Recolte', 'id_a', 'fk_id_a');
    }

    public function recoltes2()
    {
        return $this->hasMany('Kneub\Model\Recolte', 'id_a', 'id_name');
    }

    public function localites()
    {
        return $this->hasManyThrough('Kneub\Model\Localite', 'Kneub\Model\Recolte', 'id_name', 'id_loc', 'id_name', 'id_recolte');
    }

    public function nom_accepte()
    {
        return $this->belongsTo('Kneub\Model\Nom', 'fk_id_a', 'id_name');
    }
    /*
    public function descriptions()
    {
        return $this->hasMany('Kneub\Model\Description', 'fk_id_name', 'id_name')->orderBy('id_description', 'asc');
    }
    */
}
